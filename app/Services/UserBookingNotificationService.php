<?php

namespace App\Services;

use App\Models\Admin\DiyaSession;
use App\Mail\PaymentSuccessfulMail;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use App\Models\Admin\NotificationLog;
use App\Models\Dispute;
use App\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Model;

class UserBookingNotificationService
{
    private const CHANNEL = 'my_bookings';
    private const EMAIL_CHANNEL = 'email';

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
public function paymentSuccessful(
    Model $session,
    PaymentAttempt $attempt
): void {
    $notification = null;

    try {
        $session->loadMissing([
            'user',
            'sankalp',
        ]);

        $attempt->loadMissing([
            'donation',
            'user',
        ]);

        $recipient =
            $session->user?->email
            ?: $attempt->user?->email
            ?: $attempt->donation?->donor_email;

        if (!$recipient) {
            return;
        }

        $details = $this->paymentMailDetails(
            $session,
            $attempt
        );

        $messageType =
            'payment_successful_email_'.$attempt->id;

        $notification =
            NotificationLog::firstOrCreate(
                [
                    'user_id' =>
                        $session->user_id
                        ?: $attempt->user_id,

                    'channel' =>
                        self::EMAIL_CHANNEL,

                    'message_type' =>
                        $messageType,
                ],
                [
                    'recipient' =>
                        $recipient,

                    'subject' =>
                        $details['subject'],

                    'message' =>
                        'Payment '
                        .$details['formatted_amount']
                        .' received for '
                        .$details['service_name']
                        .'.',

                    'delivery_status' =>
                        'pending',
                ]
            );

        /*
         * Same payment attempt par
         * duplicate email mat bhejna.
         */
        if (!$notification->wasRecentlyCreated) {
            return;
        }

        Mail::to($recipient)->send(
            new PaymentSuccessfulMail($details)
        );

        $notification->update([
            'delivery_status' => 'sent',
            'failure_reason' => null,
            'sent_at' => now(),
        ]);

    } catch (Throwable $e) {

        /*
         * IMPORTANT:
         * Email fail hone se payment rollback
         * nahi honi chahiye.
         */

        if ($notification?->exists) {

            try {
                $notification->update([
                    'delivery_status' => 'failed',

                    'failure_reason' =>
                        substr(
                            $e->getMessage(),
                            0,
                            2000
                        ),

                    'sent_at' => null,
                ]);

            } catch (Throwable) {
                /*
                 * Notification logging failure
                 * payment ko affect nahi karega.
                 */
            }
        }

        Log::warning(
            'BhaktiDeep payment-success email failed',
            [
                'payment_attempt_id' =>
                    $attempt->id ?? null,

                'booking_type' =>
                    class_basename($session),

                'booking_id' =>
                    $session->getKey(),

                'error' =>
                    $e->getMessage(),
            ]
        );
    }
}
private function paymentMailDetails(
    Model $session,
    PaymentAttempt $attempt
): array {

    $meta = is_array($attempt->metadata)
        ? $attempt->metadata
        : [];

    $note = $session->admin_note
        ? (
            json_decode(
                $session->admin_note,
                true
            ) ?: []
        )
        : [];

    if ($session instanceof DiyaSession) {

        $session->loadMissing([
            'diya',
            'deity',
        ]);
    }

    /*
     * Service / Pooja / Hawan name
     */
    $serviceName = match (true) {

        $session instanceof HawanSession =>
            $meta['hawan_name']
            ?? $note['hawan_name']
            ?? 'Hawan',

        $session instanceof PoojaSession =>
            $meta['pooja_name']
            ?? $note['pooja_name']
            ?? 'Pooja',

        $session instanceof DiyaSession =>
            $session->diya?->name
            ?? $meta['diya_name']
            ?? $note['diya_name']
            ?? 'Diya Seva',

        default =>
            'BhaktiDeep Seva',
    };

    /*
     * Package name
     */
    $packageName = match (true) {

        $session instanceof HawanSession =>
            $session->hawan_type_title
            ?? $meta['package_name']
            ?? $note['package_name']
            ?? null,

        $session instanceof PoojaSession =>
            $session->pooja_type_title
            ?? $meta['package_name']
            ?? $note['package_name']
            ?? null,

        default =>
            null,
    };

    /*
     * Digital Pooja check
     */
    $isDigitalPooja =
        $session instanceof PoojaSession
        && $session->pooja_type === 'digital';

    /*
     * Online / Offline / Digital
     */
    $modeLabel = match (true) {

        $isDigitalPooja =>
            'Digital Pooja',

        $session instanceof DiyaSession =>
            'Diya Seva',

        $session->booking_mode === 'offline' =>
            'Offline',

        $session->booking_mode === 'online' =>
            'Online',

        default =>
            null,
    };

    /*
     * Booking reference
     */
    $typeCode = match (true) {

        $session instanceof HawanSession =>
            'HAWAN',

        $session instanceof PoojaSession =>
            'POOJA',

        $session instanceof DiyaSession =>
            'DIYA',

        default =>
            'BOOKING',
    };

    /*
     * CTA
     */
    $actionUrl = $isDigitalPooja
        ? route(
            'pooja.digital.show',
            $session
        )
        : route('user.profile');

    $actionLabel = $isDigitalPooja
        ? 'Open Digital Pooja'
        : 'View My Booking';

    /*
     * Next step message
     */
    $nextStepText = match (true) {

        $isDigitalPooja =>
            'Your Digital Pooja access is active now. Use the button below to begin your devotional session.',

        $session instanceof DiyaSession =>
            'Your payment is confirmed and your Diya Seva is now active.',

        default =>
            'Your payment is confirmed. Your booking is now waiting for Pandit acceptance. We will notify you again when it is accepted.',
    };

    /*
     * Digital expiry info
     */
    $accessText = null;

    if ($isDigitalPooja) {

        if ($session->expires_at) {

            $accessText =
                'Digital access is available until '
                .$session->expires_at
                    ->copy()
                    ->timezone(
                        config(
                            'app.timezone',
                            'Asia/Kolkata'
                        )
                    )
                    ->format(
                        'd M Y, h:i A'
                    )
                .'.';

        } elseif ($session->digital_access_minutes) {

            $accessText =
                'Digital access duration: '
                .(int) $session->digital_access_minutes
                .' minutes.';
        }
    }

    /*
     * Amount
     */
    $currency = strtoupper(
        (string) (
            $attempt->currency
            ?: 'INR'
        )
    );

    $formattedAmount =
        $currency === 'INR'
            ? '₹'.number_format(
                (float) $attempt->amount,
                2
            )
            : $currency.' '.number_format(
                (float) $attempt->amount,
                2
            );

    /*
     * Payment time
     */
    $paidAt =
        $attempt->paid_at
        ?: now();

    return [

        'subject' =>
            'Payment successful - '
            .$serviceName
            .' | BhaktiDeep',

        'greeting_name' =>
            $session->user?->name
            ?: $session->sankalp?->full_name
            ?: (
                $meta['donor_name']
                ?? 'Devotee'
            ),

        'service_name' =>
            $serviceName,

        'package_name' =>
            $packageName,

        'mode_label' =>
            $modeLabel,

        'booking_date' =>
            $session->booking_date
                ?->format('d M Y'),

        'slot' =>
            $session->slot,

        'booking_reference' =>
            'BD-'
            .$typeCode
            .'-'
            .$session->getKey(),

        'formatted_amount' =>
            $formattedAmount,

        'payment_id' =>
            $attempt->gateway_payment_id,

        'receipt_number' =>
            $attempt->donation?->receipt_number,

        'paid_at' =>
            $paidAt
                ->copy()
                ->timezone(
                    config(
                        'app.timezone',
                        'Asia/Kolkata'
                    )
                )
                ->format(
                    'd M Y, h:i A'
                ),

        'next_step_text' =>
            $nextStepText,

        'access_text' =>
            $accessText,

        'action_url' =>
            $actionUrl,

        'action_label' =>
            $actionLabel,
    ];
}
    private function diyaName(DiyaSession $session): string
    {
        $meta = $session->admin_note ? (json_decode($session->admin_note, true) ?: []) : [];

        return $session->diya?->name ?? $meta['diya_name'] ?? 'Diya';
    }
}
