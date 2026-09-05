<?php

namespace App\Services;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Dispute;
use App\Models\Pandit\PanditNotification;
use App\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Model;

class PanditDisputeNotificationService
{
    public function reportUnderReview(Dispute $dispute): void
    {
        $this->notifyOnce(
            $this->booking($dispute),
            'Report under admin review',
            'Report #'.$dispute->id.' is under admin review. Your payout remains on hold.'
        );
    }

    public function disputeResolvedForUser(Dispute $dispute, PaymentRefund $refund): void
    {
        $booking = $this->booking($dispute);

        $this->notifyOnce(
            $booking,
            'Report resolved in user favour',
            'Report #'.$dispute->id.' was resolved in the user\'s favour. Refund ₹'.$this->amount($refund).' has been initiated to the user. Your payout for Booking #'.$booking?->id.' is cancelled.'
        );
    }

    public function refundProcessed(PaymentRefund $refund, ?Dispute $dispute = null): void
    {
        $dispute ??= Dispute::where('payment_refund_id', $refund->id)->first();

        if (!$dispute) {
            return;
        }

        $booking = $this->booking($dispute);

        $this->notifyOnce(
            $booking,
            'Refund processed',
            'Refund ₹'.$this->amount($refund).' was processed to the user. No payout is due for Booking #'.$booking?->id.'.'
        );
    }

    public function disputeResolvedForPandit(Dispute $dispute): void
    {
        $this->notifyOnce(
            $this->booking($dispute),
            'Report resolved in pandit favour',
            'Report #'.$dispute->id.' was resolved in your favour. No refund was issued. Your payout is now READY.'
        );
    }

    private function notifyOnce(?Model $booking, string $title, string $message): void
    {
        if (!$booking?->pandit_id) {
            return;
        }

        PanditNotification::firstOrCreate(
            [
                'pandit_id' => $booking->pandit_id,
                'title' => $title,
                'message' => $message,
            ],
            ['is_read' => false]
        );
    }

    private function booking(Dispute $dispute): ?Model
    {
        if (!in_array($dispute->disputable_type, [HawanSession::class, PoojaSession::class], true)) {
            return null;
        }

        return $dispute->disputable_type::find($dispute->disputable_id);
    }

    private function amount(PaymentRefund $refund): string
    {
        return number_format((float) $refund->amount);
    }
}
