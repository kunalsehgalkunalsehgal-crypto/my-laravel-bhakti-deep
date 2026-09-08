<?php

namespace App\Services;

use App\Models\Admin\DiyaSession;
use App\Models\Admin\NotificationLog;
use App\Models\Dispute;
use App\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Model;

class UserBookingNotificationService
{
    private const CHANNEL = 'my_bookings';
    private const SUBJECT = 'BhaktiDeep booking update';

    public function reportSubmitted(Model $booking, Dispute $dispute): void
    {
        $this->notifyOnce(
            $dispute,
            'report_submitted_'.$dispute->id,
            'Your issue for Booking #'.$booking->id.' has been submitted. Report #'.$dispute->id.' is now under review.'
        );
    }

    public function reportUnderReview(Dispute $dispute): void
    {
        $this->notifyOnce(
            $dispute,
            'report_under_review_'.$dispute->id,
            'Your report #'.$dispute->id.' is under review.'
        );
    }

    public function disputeResolvedForUser(Dispute $dispute): void
    {
        $this->notifyOnce(
            $dispute,
            'report_user_favour_'.$dispute->id,
            'Your report #'.$dispute->id.' was resolved in your favour. Refund has been initiated.'
        );
    }

    public function refundProcessed(PaymentRefund $refund, ?Dispute $dispute = null): void
    {
        $dispute ??= Dispute::with('user')->where('payment_refund_id', $refund->id)->first();

        if (!$dispute || (int) $dispute->user_id !== (int) $refund->user_id) {
            return;
        }

        $this->notifyOnce(
            $dispute,
            'report_refund_processed_'.$refund->id,
            'Refund Processed ₹'.number_format((float) $refund->amount)
        );
    }

    public function disputeResolvedForPandit(Dispute $dispute): void
    {
        $this->notifyOnce(
            $dispute,
            'report_pandit_favour_'.$dispute->id,
            'Your report #'.$dispute->id.' has been reviewed and closed. No refund was issued.'
        );
    }

    public function diyaPaymentSuccessful(DiyaSession $session): void
    {
        $this->notifyDiyaOnce(
            $session,
            'diya_payment_successful_'.$session->id,
            'Payment successful for your '.$this->diyaName($session).'. Your diya is now glowing.'
        );
    }

    public function diyaStarted(DiyaSession $session): void
    {
        $this->notifyDiyaOnce(
            $session,
            'diya_started_'.$session->id,
            'Your scheduled '.$this->diyaName($session).' has started.'
        );
    }

    public function diyaCompleted(DiyaSession $session): void
    {
        $this->notifyDiyaOnce(
            $session,
            'diya_completed_'.$session->id,
            'Your '.$this->diyaName($session).' has completed.'
        );
    }

    private function notifyOnce(Dispute $dispute, string $messageType, string $message): void
    {
        $dispute->loadMissing('user');

        NotificationLog::firstOrCreate(
            [
                'user_id' => $dispute->user_id,
                'channel' => self::CHANNEL,
                'message_type' => $messageType,
            ],
            [
                'recipient' => $dispute->user?->email,
                'subject' => self::SUBJECT,
                'message' => $message,
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]
        );
    }

    private function notifyDiyaOnce(DiyaSession $session, string $messageType, string $message): void
    {
        $session->loadMissing(['user', 'diya', 'deity']);

        if (!$session->user_id) {
            return;
        }

        NotificationLog::firstOrCreate(
            [
                'user_id' => $session->user_id,
                'channel' => self::CHANNEL,
                'message_type' => $messageType,
            ],
            [
                'recipient' => $session->user?->email,
                'subject' => self::SUBJECT,
                'message' => $message,
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]
        );
    }

    private function diyaName(DiyaSession $session): string
    {
        $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];

        return $session->diya?->name ?? $meta['diya_name'] ?? 'Diya';
    }
}
