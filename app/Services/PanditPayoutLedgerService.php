<?php

namespace App\Services;

use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\SessionCompletionProof;
use App\Models\VideoMeetingAttendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PanditPayoutLedgerService
{
    public function holdForSuccessfulPayment(Model $booking, PaymentAttempt $attempt): ?PanditPayout
    {
        return $this->record($booking, $attempt, PanditPayout::STATUS_HOLD, 'payment_successful');
    }

    public function syncForBooking(Model $booking, string $reason = 'booking_sync'): ?PanditPayout
    {
        return DB::transaction(function () use ($booking, $reason) {
            $lockedBooking = $this->lockedBooking($booking);
            $attempt = $this->lockedPaidAttempt($lockedBooking);

            if (!$attempt) {
                return null;
            }

            return $this->upsert($lockedBooking, $attempt, $this->statusFor($lockedBooking), $reason);
        });
    }

    public function readyForPanditFavour(Model $booking): ?PanditPayout
    {
        return DB::transaction(function () use ($booking) {
            $lockedBooking = $this->lockedBooking($booking);
            $attempt = $this->lockedPaidAttempt($lockedBooking);

            if (!$attempt) {
                return null;
            }

            return $this->upsert($lockedBooking, $attempt, PanditPayout::STATUS_READY, 'dispute_pandit_favour');
        });
    }

    public function cancelForRefund(Model $booking, ?PaymentAttempt $attempt = null, string $reason = 'user_refunded'): ?PanditPayout
    {
        return DB::transaction(function () use ($booking, $attempt, $reason) {
            $lockedBooking = $this->lockedBooking($booking);
            $lockedAttempt = $attempt
                ? PaymentAttempt::with('donation')->whereKey($attempt->id)->lockForUpdate()->first()
                : $this->lockedPaidAttempt($lockedBooking);

            if (!$lockedAttempt) {
                return null;
            }

            return $this->upsert($lockedBooking, $lockedAttempt, PanditPayout::STATUS_CANCELLED, $reason);
        });
    }

    private function record(Model $booking, PaymentAttempt $attempt, string $status, string $reason): ?PanditPayout
    {
        return DB::transaction(function () use ($booking, $attempt, $status, $reason) {
            return $this->upsert(
                $this->lockedBooking($booking),
                PaymentAttempt::with('donation')->whereKey($attempt->id)->lockForUpdate()->firstOrFail(),
                $status,
                $reason
            );
        });
    }

    private function lockedBooking(Model $booking): Model
    {
        return $booking::query()->whereKey($booking->getKey())->lockForUpdate()->firstOrFail();
    }

    private function lockedPaidAttempt(Model $booking): ?PaymentAttempt
    {
        if (!$booking->latest_payment_attempt_id) {
            return null;
        }

        $attempt = PaymentAttempt::with('donation')
            ->whereKey($booking->latest_payment_attempt_id)
            ->lockForUpdate()
            ->first();

        if (!$attempt || $attempt->status !== PaymentAttempt::STATUS_PAID) {
            return null;
        }

        if ($attempt->payable_type !== get_class($booking) || (int) $attempt->payable_id !== (int) $booking->getKey()) {
            return null;
        }

        return $attempt;
    }

    private function statusFor(Model $booking): string
    {
        if (
            $booking->payment_status === 'refunded'
            || $booking->status === 'cancelled_by_pandit'
            || $this->hasResolvedRefundUserDispute($booking)
        ) {
            return PanditPayout::STATUS_CANCELLED;
        }

        if ($this->hasOpenDispute($booking)) {
            return PanditPayout::STATUS_HOLD;
        }

        if ($this->hasResolvedPanditFavourDispute($booking)) {
            return PanditPayout::STATUS_READY;
        }

        if (
            $booking->payment_status === 'paid'
            && $booking->status === 'completed'
            && $booking->completed_at
            && $booking->videoMeetingAttendances()->where('event_type', VideoMeetingAttendance::EVENT_MEETING_ENDED)->exists()
            && $booking->completionProofs()
                ->where('pandit_id', $booking->pandit_id)
                ->whereNotNull('submitted_at')
                ->where('status', '!=', SessionCompletionProof::STATUS_REJECTED)
                ->exists()
            && $booking->userConfirmations()
                ->where('user_id', $booking->user_id)
                ->whereIn('status', [BookingUserConfirmation::STATUS_CONFIRMED, BookingUserConfirmation::STATUS_AUTO_CONFIRMED])
                ->whereNotNull('confirmed_at')
                ->exists()
        ) {
            return PanditPayout::STATUS_READY;
        }

        return PanditPayout::STATUS_HOLD;
    }

    private function upsert(Model $booking, PaymentAttempt $attempt, string $status, string $reason): ?PanditPayout
    {
        if (!$booking->pandit_id) {
            return null;
        }

        if ($attempt->payable_type !== get_class($booking) || (int) $attempt->payable_id !== (int) $booking->getKey()) {
            return null;
        }

        $payout = PanditPayout::query()
            ->where('payment_attempt_id', $attempt->id)
            ->where('payout_type', PanditPayout::TYPE_BOOKING)
            ->lockForUpdate()
            ->first();

        if (!$payout) {
            $payout = PanditPayout::query()
                ->where('session_type', get_class($booking))
                ->where('session_id', $booking->getKey())
                ->where('payout_type', PanditPayout::TYPE_BOOKING)
                ->lockForUpdate()
                ->first();
        }

        if ($payout && $payout->status === PanditPayout::STATUS_PAID) {
            return $payout;
        }

        $amounts = $this->amounts($booking, $attempt, $status);
        $now = now();
        $existingMetadata = $payout?->metadata ?: [];
        $metadata = array_merge($existingMetadata, [
            'last_ledger_reason' => $reason,
            'last_synced_at' => $now->toIso8601String(),
            'payout_provider_configured' => $this->providerConfigured(),
            'payout_provider_pending' => !$this->providerConfigured(),
        ]);

        $data = array_merge($amounts, [
            'pandit_id' => $booking->pandit_id,
            'payment_attempt_id' => $attempt->id,
            'donation_id' => $attempt->donation_id,
            'session_type' => get_class($booking),
            'session_id' => $booking->getKey(),
            'payout_type' => PanditPayout::TYPE_BOOKING,
            'currency' => $attempt->currency,
            'status' => $status,
            'eligible_at' => $status === PanditPayout::STATUS_READY ? ($payout?->eligible_at ?: $now) : null,
            'approved_at' => $status === PanditPayout::STATUS_READY ? ($payout?->approved_at ?: $now) : null,
            'cancelled_at' => $status === PanditPayout::STATUS_CANCELLED ? ($payout?->cancelled_at ?: $now) : null,
            'metadata' => $metadata,
        ]);

        if ($payout) {
            $payout->update($data);

            return $payout->fresh();
        }

        return PanditPayout::create($data);
    }

    private function amounts(Model $booking, PaymentAttempt $attempt, string $status): array
    {
        $meta = array_merge($attempt->metadata ?: [], $this->bookingMeta($booking));
        $bookingAmount = (float) $attempt->amount;
        $platformAmount = (float) ($meta['platform_amount'] ?? $meta['platform_fee'] ?? 0);
        $panditAmount = (float) ($meta['pandit_amount'] ?? max($bookingAmount - $platformAmount, 0));

        if ($status === PanditPayout::STATUS_CANCELLED) {
            $panditAmount = 0;
        }

        return [
            'booking_amount' => $bookingAmount,
            'pandit_amount' => $panditAmount,
            'platform_amount' => $platformAmount,
            'gross_amount' => $bookingAmount,
            'platform_fee' => $platformAmount,
            'dakshina_amount' => (float) ($meta['dakshina'] ?? $meta['donation_amount'] ?? 0),
            'payout_amount' => $panditAmount,
        ];
    }

    private function bookingMeta(Model $booking): array
    {
        return $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];
    }

    private function hasOpenDispute(Model $booking): bool
    {
        return Dispute::query()
            ->where('disputable_type', get_class($booking))
            ->where('disputable_id', $booking->getKey())
            ->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW])
            ->exists();
    }

    private function hasResolvedRefundUserDispute(Model $booking): bool
    {
        return $this->resolvedDispute($booking, Dispute::RESOLUTION_REFUND_USER);
    }

    private function hasResolvedPanditFavourDispute(Model $booking): bool
    {
        return $this->resolvedDispute($booking, Dispute::RESOLUTION_PANDIT_FAVOUR);
    }

    private function resolvedDispute(Model $booking, string $resolution): bool
    {
        return Dispute::query()
            ->where('disputable_type', get_class($booking))
            ->where('disputable_id', $booking->getKey())
            ->where('status', Dispute::STATUS_RESOLVED)
            ->where('resolution', $resolution)
            ->exists();
    }

    private function providerConfigured(): bool
    {
        return filled(config('services.payouts.provider'));
    }
}
