<?php

namespace App\Services;

use App\Mail\BookingTodayReminderMail;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PoojaSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingTodayReminderService
{
    private const CHANNEL = 'email';

    private const TIMEZONE = 'Asia/Kolkata';

    public function send(Model $session, string $type): int
    {
        if (! in_array($type, ['pooja', 'hawan'], true)) {
            return 0;
        }

        if (! $session instanceof PoojaSession && ! $session instanceof HawanSession) {
            return 0;
        }

        if ($session instanceof PoojaSession && $session->pooja_type === 'digital') {
            return 0;
        }

        if ($session->payment_status !== 'paid' || $session->status !== 'confirmed') {
            return 0;
        }

        if (! $session->booking_date || ! $session->booking_date->isSameDay(now(self::TIMEZONE))) {
            return 0;
        }

        $session->loadMissing(['user', 'pandit', 'sankalp', 'videoMeeting']);

        $mode = $session->booking_mode ?: 'online';

        if ($mode === 'online' && ! $session->videoMeeting) {
            // Online reminder tabhi bhejo jab live meeting actually ready ho.
            return 0;
        }

        $common = $this->commonDetails($session, $type);
        $sent = 0;

        if ($session->user?->email) {
            $details = array_merge($common, $this->userDetails($session, $type));

            if ($this->sendOnce(
                $session->user_id,
                $session->user->email,
                'today_booking_user_'.$type.'_'.$session->id.'_'.$session->booking_date->format('Ymd'),
                $details
            )) {
                $sent++;
            }
        }

        if ($session->pandit?->email) {
            $details = array_merge($common, $this->panditDetails($session, $type));

            if ($this->sendOnce(
                null,
                $session->pandit->email,
                'today_booking_pandit_'.$type.'_'.$session->id.'_'.$session->booking_date->format('Ymd'),
                $details
            )) {
                $sent++;
            }
        }

        return $sent;
    }

    private function commonDetails(Model $session, string $type): array
    {
        $meta = $session->admin_note
            ? (json_decode($session->admin_note, true) ?: [])
            : [];

        $serviceName = $type === 'hawan'
            ? ($meta['hawan_name'] ?? 'Hawan')
            : ($meta['pooja_name'] ?? 'Pooja');

        $packageName = $type === 'hawan'
            ? ($session->hawan_type_title ?? $meta['package_name'] ?? null)
            : ($session->pooja_type_title ?? $meta['package_name'] ?? null);

        $mode = $session->booking_mode ?: 'online';
        $location = collect([$session->city, $session->state])
            ->filter()
            ->implode(', ');

        return [
            'service_name' => $serviceName,
            'package_name' => $packageName,
            'mode_label' => $mode === 'offline' ? 'Offline' : 'Online',
            'booking_date' => $session->booking_date->format('d M Y'),
            'slot' => $session->slot ?: 'Time not available',
            'pandit_name' => $session->pandit?->pandit_name ?: $session->pandit?->full_name ?: 'Pandit Ji',
            'devotee_name' => $session->user?->name ?: $session->sankalp?->full_name ?: 'Devotee',
            'location' => $location ?: null,
            'booking_reference' => 'BD-'.strtoupper($type).'-'.$session->id,
        ];
    }

    private function userDetails(Model $session, string $type): array
    {
        $mode = $session->booking_mode ?: 'online';

        return [
            'subject' => 'Today: Your '.$this->ritualLabel($type).' at '.$session->slot.' | BhaktiDeep',
            'recipient_name' => $session->user?->name ?: $session->sankalp?->full_name ?: 'Devotee',
            'recipient_role' => 'user',
            'heading' => 'Aaj aapki '.$this->ritualLabel($type).' hai',
            'instruction_text' => $mode === 'online'
                ? 'Please session time se 10 minutes pehle BhaktiDeep me login karke Live Sessions page par aa jaiye.'
                : 'Please session time se 15 minutes pehle ready rahein. Pandit Ji aapki booked location par seva ke liye aayenge.',
            'action_url' => $mode === 'online'
                ? route('live.sessions')
                : route('user.profile'),
            'action_label' => $mode === 'online'
                ? 'Open Live Sessions'
                : 'View My Booking',
        ];
    }

    private function panditDetails(Model $session, string $type): array
    {
        $mode = $session->booking_mode ?: 'online';

        return [
            'subject' => 'Today: '.$this->ritualLabel($type).' booking at '.$session->slot.' | BhaktiDeep',
            'recipient_name' => $session->pandit?->pandit_name ?: $session->pandit?->full_name ?: 'Pandit Ji',
            'recipient_role' => 'pandit',
            'heading' => 'Aaj aapki assigned '.$this->ritualLabel($type).' booking hai',
            'instruction_text' => $mode === 'online'
                ? 'Please session time se 10 minutes pehle BhaktiDeep Pandit Panel ke Live Sessions section me aa jaiye aur meeting start karne ke liye ready rahein.'
                : 'Please booked time se pehle devotee ki location par pahunchne ke liye ready rahein.',
            'action_url' => $mode === 'online'
                ? route('pandit.live-sessions.index')
                : route('pandit.bookings.show', ['type' => $type, 'id' => $session->id]),
            'action_label' => $mode === 'online'
                ? 'Open Pandit Live Sessions'
                : 'View Booking',
        ];
    }

    private function sendOnce(?int $userId, string $recipient, string $messageType, array $details): bool
    {
        $notification = NotificationLog::firstOrCreate(
            [
                'user_id' => $userId,
                'channel' => self::CHANNEL,
                'message_type' => $messageType,
                'recipient' => $recipient,
            ],
            [
                'subject' => $details['subject'],
                'message' => $details['heading'].' - '.$details['slot'],
                'delivery_status' => 'pending',
            ]
        );

        if (! $notification->wasRecentlyCreated && $notification->delivery_status === 'sent') {
            return false;
        }

        try {
            Mail::to($recipient)->send(new BookingTodayReminderMail($details));

            $notification->update([
                'subject' => $details['subject'],
                'message' => $details['heading'].' - '.$details['slot'],
                'delivery_status' => 'sent',
                'failure_reason' => null,
                'sent_at' => now(),
            ]);

            return true;
        } catch (Throwable $e) {
            $notification->update([
                'delivery_status' => 'failed',
                'failure_reason' => substr($e->getMessage(), 0, 2000),
                'sent_at' => null,
            ]);

            Log::warning('BhaktiDeep today booking reminder email failed', [
                'recipient' => $recipient,
                'message_type' => $messageType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function ritualLabel(string $type): string
    {
        return $type === 'hawan' ? 'Hawan' : 'Pooja';
    }
}
