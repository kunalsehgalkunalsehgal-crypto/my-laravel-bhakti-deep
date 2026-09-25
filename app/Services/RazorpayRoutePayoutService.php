<?php

namespace App\Services;

use App\Exceptions\RazorpayRouteException;
use App\Models\PanditPayout;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RazorpayRoutePayoutService
{
    public function createTransfer(PanditPayout $payout, bool $reconcileFirst = false): array
    {
        $payout->loadMissing(['paymentAttempt', 'pandit.bankDetail']);
        $attempt = $payout->paymentAttempt;
        $bank = $payout->pandit?->bankDetail;

        if (! $attempt?->gateway_payment_id) {
            throw new RazorpayRouteException('Razorpay payment ID is missing.');
        }

        if (! $bank?->isRazorpayPayoutReady()) {
            throw new RazorpayRouteException('Pandit Razorpay Route account is not activated and bank-verified.');
        }

        if ((float) $payout->pandit_amount <= 0) {
            throw new RazorpayRouteException('Pandit transfer amount must be greater than zero.');
        }

        $this->assertCredentials();

        if ($reconcileFirst) {
            $existing = $this->findExistingTransfer($payout);

            if ($existing) {
                return $existing;
            }
        }

        try {
            $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
                ->asJson()
                ->post($this->paymentTransfersUrl($attempt->gateway_payment_id), [
                    'transfers' => [[
                        'account' => $bank->razorpay_linked_account_id,
                        'amount' => (int) round(((float) $payout->pandit_amount) * 100),
                        'currency' => $payout->currency,
                        'notes' => [
                            'bhaktideep_payout_uuid' => $payout->uuid,
                            'booking_id' => (string) $payout->session_id,
                        ],
                        'linked_account_notes' => ['booking_id'],
                        'on_hold' => false,
                    ]],
                ]);
        } catch (ConnectionException $exception) {
            throw new RazorpayRouteException('Razorpay Route connection failed: '.$exception->getMessage(), true);
        }

        if (! $response->successful()) {
            $message = $response->json('error.description') ?: 'Razorpay Route transfer request failed.';
            throw new RazorpayRouteException($message, $response->serverError());
        }

        $transfer = $response->json('items.0') ?: $response->json('transfers.0') ?: $response->json();

        if (blank($transfer['id'] ?? null)) {
            throw new RazorpayRouteException('Razorpay Route response did not include a transfer ID.', true);
        }

        return $transfer;
    }

    public function applyWebhook(string $event, array $transfer): void
    {
        $transferId = $transfer['id'] ?? null;
        $payoutUuid = $transfer['notes']['bhaktideep_payout_uuid'] ?? null;

        if (! $transferId && ! $payoutUuid) {
            return;
        }

        DB::transaction(function () use ($event, $transfer, $transferId, $payoutUuid) {
            $payout = $transferId
                ? PanditPayout::query()
                    ->where(fn ($query) => $query->where('razorpay_transfer_id', $transferId)->orWhere('provider_payout_id', $transferId))
                    ->lockForUpdate()
                    ->first()
                : null;

            $payout ??= $payoutUuid
                ? PanditPayout::where('uuid', $payoutUuid)->lockForUpdate()->first()
                : null;

            if (! $payout || $payout->status === PanditPayout::STATUS_CANCELLED) {
                return;
            }

            $payout->loadMissing(['paymentAttempt', 'pandit.bankDetail']);

            if (($payout->razorpay_transfer_id && $transferId && $payout->razorpay_transfer_id !== $transferId)
                || (($transfer['source'] ?? null) && $transfer['source'] !== $payout->paymentAttempt?->gateway_payment_id)
                || (($transfer['recipient'] ?? null) && $transfer['recipient'] !== $payout->pandit?->bankDetail?->razorpay_linked_account_id)
                || (isset($transfer['amount']) && (int) $transfer['amount'] !== (int) round(((float) $payout->pandit_amount) * 100))
            ) {
                return;
            }

            $metadata = array_merge($payout->metadata ?: [], ['razorpay_transfer' => $transfer]);
            $common = [
                'razorpay_transfer_id' => $transferId ?: $payout->razorpay_transfer_id,
                'provider_payout_id' => $transferId ?: $payout->provider_payout_id,
                'gateway_status' => $transfer['status'] ?? (string) str($event)->after('transfer.'),
                'metadata' => $metadata,
            ];

            if ($event === 'transfer.processed') {
                $payout->update($common + [
                    'status' => PanditPayout::STATUS_PAID,
                    'paid_at' => $payout->paid_at ?: now(),
                    'failed_at' => null,
                    'last_error' => null,
                ]);

                return;
            }

            if ($event === 'transfer.failed' && $payout->status !== PanditPayout::STATUS_PAID) {
                $payout->update($common + [
                    'status' => PanditPayout::STATUS_FAILED,
                    'failed_at' => now(),
                    'last_error' => $this->transferError($transfer) ?: 'Razorpay Route transfer failed.',
                ]);
            }
        });
    }

    private function findExistingTransfer(PanditPayout $payout): ?array
    {
        try {
            $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
                ->acceptJson()
                ->get($this->paymentTransfersUrl($payout->paymentAttempt->gateway_payment_id));
        } catch (ConnectionException $exception) {
            throw new RazorpayRouteException('Could not reconcile the previous Route transfer: '.$exception->getMessage(), true);
        }

        if (! $response->successful()) {
            throw new RazorpayRouteException(
                $response->json('error.description') ?: 'Could not reconcile the previous Route transfer.',
                true
            );
        }

        $accountId = $payout->pandit->bankDetail->razorpay_linked_account_id;
        $amount = (int) round(((float) $payout->pandit_amount) * 100);

        return collect($response->json('items', []))->first(fn ($transfer) => ($transfer['notes']['bhaktideep_payout_uuid'] ?? null) === $payout->uuid
            && ($transfer['recipient'] ?? null) === $accountId
            && (int) ($transfer['amount'] ?? 0) === $amount
        );
    }

    private function assertCredentials(): void
    {
        if (blank(config('services.razorpay.key_id')) || blank(config('services.razorpay.key_secret'))) {
            throw new RazorpayRouteException('Razorpay API credentials are not configured.');
        }
    }

    private function paymentTransfersUrl(string $paymentId): string
    {
        return rtrim(config('services.razorpay.base_url'), '/').'/v1/payments/'.$paymentId.'/transfers';
    }

    public function transferError(array $transfer): ?string
    {
        $error = $transfer['error'] ?? null;

        if (is_string($error)) {
            return $error;
        }

        return $error['description'] ?? $error['reason'] ?? null;
    }
}
