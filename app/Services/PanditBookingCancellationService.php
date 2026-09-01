<?php

namespace App\Services;

use App\Mail\BookingRefundMail;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PanditBookingCancellationService
{
    public function cancel(string $type, int $id, Pandit $pandit, string $reason): array
    {
        return DB::transaction(function () use ($type, $id, $pandit, $reason) {
            $session = $this->query($type)
                ->with(['user', 'latestPaymentAttempt.donation'])
                ->whereKey($id)
                ->where('pandit_id', $pandit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->payment_status !== 'paid') {
                throw ValidationException::withMessages(['booking' => 'Only paid bookings can be cancelled by pandit.']);
            }

            if ($session->status === 'cancelled_by_pandit') {
                throw ValidationException::withMessages(['booking' => 'This booking is already cancelled.']);
            }

            $attempt = $session->latestPaymentAttempt;
            if (!$attempt || $attempt->status !== PaymentAttempt::STATUS_PAID) {
                throw ValidationException::withMessages(['payment' => 'Paid payment attempt is missing.']);
            }

            if ($attempt->refunds()->whereNotIn('status', [PaymentRefund::STATUS_FAILED, PaymentRefund::STATUS_REJECTED, PaymentRefund::STATUS_CANCELLED])->exists()) {
                throw ValidationException::withMessages(['payment' => 'Refund is already initiated for this booking.']);
            }

            $refundData = app(RazorpayPaymentService::class)->refund($attempt, $reason);
            $processed = ($refundData['status'] ?? null) === 'processed';
            $amount = (float) $attempt->amount;
            $initiatedMessage = 'Refund of ₹'.number_format($amount).' initiated.';
            $processedMessage = 'Refund Processed ₹'.number_format($amount);

            $refund = PaymentRefund::create([
                'payment_attempt_id' => $attempt->id,
                'donation_id' => $attempt->donation_id,
                'user_id' => $session->user_id,
                'amount' => $amount,
                'currency' => $attempt->currency,
                'reason' => $reason,
                'status' => $processed ? PaymentRefund::STATUS_REFUNDED : PaymentRefund::STATUS_PROCESSING,
                'gateway_refund_id' => $refundData['id'] ?? null,
                'requested_at' => now(),
                'processed_at' => $processed ? now() : null,
                'metadata' => $refundData,
            ]);

            $session->update([
                'status' => 'cancelled_by_pandit',
                'payment_status' => $processed ? 'refunded' : 'paid',
                'pandit_cancel_reason' => $reason,
                'pandit_cancelled_at' => now(),
                'cancelled_by_pandit_id' => $pandit->id,
            ]);

            $attempt->donation?->update([
                'payment_status' => $processed ? 'refunded' : 'paid',
                'refunded_amount' => $processed ? $amount : 0,
            ]);

            PanditPayout::where('session_type', get_class($session))
                ->where('session_id', $session->id)
                ->update(['status' => PanditPayout::STATUS_CANCELLED, 'payout_amount' => 0]);

            PanditPayout::create([
                'pandit_id' => $pandit->id,
                'payment_attempt_id' => $attempt->id,
                'donation_id' => $attempt->donation_id,
                'session_type' => get_class($session),
                'session_id' => $session->id,
                'payout_type' => PanditPayout::TYPE_BOOKING,
                'gross_amount' => $amount,
                'platform_fee' => 0,
                'dakshina_amount' => 0,
                'payout_amount' => 0,
                'currency' => $attempt->currency,
                'status' => PanditPayout::STATUS_CANCELLED,
                'metadata' => ['not_eligible_reason' => 'cancelled_by_pandit'],
            ]);

            $this->log($attempt, $refund, $processed);
            $this->notifyUser($session, $initiatedMessage);

            if ($processed) {
                $this->notifyUser($session, $processedMessage);
            }

            return ['amount' => $amount, 'processed' => $processed];
        });
    }

    private function query(string $type)
    {
        return match ($type) {
            'hawan' => HawanSession::query(),
            'pooja' => PoojaSession::query(),
            default => abort(404),
        };
    }

    private function log(PaymentAttempt $attempt, PaymentRefund $refund, bool $processed): void
    {
        PaymentLog::create([
            'donation_id' => $attempt->donation_id,
            'payment_attempt_id' => $attempt->id,
            'loggable_type' => $attempt->payable_type,
            'loggable_id' => $attempt->payable_id,
            'user_id' => $attempt->user_id,
            'gateway' => 'razorpay_test',
            'event_type' => $processed ? 'refund_processed' : 'refund_initiated',
            'order_id' => $attempt->gateway_order_id,
            'payment_id' => $attempt->gateway_payment_id,
            'status' => $processed ? 'refunded' : 'processing',
            'occurred_at' => now(),
            'amount' => $refund->amount,
            'payload' => ['refund_id' => $refund->gateway_refund_id],
        ]);
    }

    private function notifyUser(Model $session, string $message): void
    {
        if (!$session->user) {
            return;
        }

        NotificationLog::create([
            'user_id' => $session->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'booking_refund',
            'recipient' => $session->user->email,
            'subject' => 'BhaktiDeep booking refund update',
            'message' => $message,
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        Mail::to($session->user->email)->send(new BookingRefundMail($message));
    }
}
