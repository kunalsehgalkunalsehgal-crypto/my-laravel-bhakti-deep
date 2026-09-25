<?php

namespace App\Jobs;

use App\Exceptions\RazorpayRouteException;
use App\Models\PanditPayout;
use App\Services\PanditPayoutAutomationService;
use App\Services\RazorpayRoutePayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProcessPanditPayout implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 3600;

    public function __construct(public int $payoutId, public string $processingToken = '')
    {
        $this->processingToken = $processingToken ?: (string) Str::uuid();
    }

    public function uniqueId(): string
    {
        return (string) $this->payoutId;
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(RazorpayRoutePayoutService $route): void
    {
        $payout = $this->beginProcessing();

        if (! $payout) {
            return;
        }

        try {
            $transfer = $route->createTransfer($payout, $payout->transfer_attempts > 1);
            $this->saveTransfer($transfer, $route);
        } catch (RazorpayRouteException $exception) {
            $this->markFailed($exception->getMessage());

            if ($exception->retryable) {
                throw $exception;
            }
        } catch (Throwable $exception) {
            $this->markFailed($exception->getMessage());
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception?->getMessage() ?: 'Route payout job exhausted its retries.');
    }

    private function beginProcessing(): ?PanditPayout
    {
        return DB::transaction(function () {
            if (! app(PanditPayoutAutomationService::class)->enabled()) {
                return null;
            }

            $payout = PanditPayout::whereKey($this->payoutId)->lockForUpdate()->first();

            if (! $payout
                || in_array($payout->status, [PanditPayout::STATUS_PAID, PanditPayout::STATUS_CANCELLED], true)
                || $payout->razorpay_transfer_id
            ) {
                return null;
            }

            if ($payout->status === PanditPayout::STATUS_PROCESSING
                && $payout->processing_token !== $this->processingToken
                && $payout->processing_started_at?->gt(now()->subMinutes(15))
            ) {
                return null;
            }

            if (! in_array($payout->status, [PanditPayout::STATUS_READY, PanditPayout::STATUS_FAILED, PanditPayout::STATUS_PROCESSING], true)) {
                return null;
            }

            $payout->update([
                'status' => PanditPayout::STATUS_PROCESSING,
                'transfer_attempts' => $payout->transfer_attempts + 1,
                'processing_token' => $this->processingToken,
                'processing_started_at' => now(),
                'failed_at' => null,
                'last_error' => null,
            ]);

            return $payout->fresh(['paymentAttempt', 'pandit.bankDetail']);
        });
    }

    private function saveTransfer(array $transfer, RazorpayRoutePayoutService $route): void
    {
        DB::transaction(function () use ($transfer, $route) {
            $payout = PanditPayout::whereKey($this->payoutId)->lockForUpdate()->first();

            if (! $payout || in_array($payout->status, [PanditPayout::STATUS_PAID, PanditPayout::STATUS_CANCELLED], true)) {
                return;
            }

            $status = $transfer['status'] ?? 'pending';
            $metadata = array_merge($payout->metadata ?: [], ['razorpay_transfer' => $transfer]);
            $updates = [
                'razorpay_transfer_id' => $transfer['id'],
                'provider_payout_id' => $transfer['id'],
                'gateway_status' => $status,
                'metadata' => $metadata,
            ];

            if ($status === 'processed') {
                $updates += [
                    'status' => PanditPayout::STATUS_PAID,
                    'paid_at' => now(),
                    'failed_at' => null,
                    'last_error' => null,
                ];
            } elseif (in_array($status, ['failed', 'reversed'], true)) {
                $updates += [
                    'status' => PanditPayout::STATUS_FAILED,
                    'failed_at' => now(),
                    'last_error' => $route->transferError($transfer) ?: 'Razorpay Route transfer failed.',
                ];
            } else {
                $updates += ['status' => PanditPayout::STATUS_PROCESSING];
            }

            $payout->update($updates);
        });
    }

    private function markFailed(string $message): void
    {
        DB::transaction(function () use ($message) {
            $payout = PanditPayout::whereKey($this->payoutId)->lockForUpdate()->first();

            if (! $payout || in_array($payout->status, [PanditPayout::STATUS_PAID, PanditPayout::STATUS_CANCELLED], true)) {
                return;
            }

            $payout->update([
                'status' => PanditPayout::STATUS_FAILED,
                'failed_at' => now(),
                'last_error' => Str::limit($message, 5000, ''),
            ]);
        });
    }
}
