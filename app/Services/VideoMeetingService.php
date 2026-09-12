<?php

namespace App\Services;

use App\Contracts\VideoMeetingProvider;
use App\Models\VideoMeeting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VideoMeetingService
{
    public function __construct(private readonly VideoMeetingProvider $provider)
    {
    }

    public function createForSessionIfReady(Model $session): ?VideoMeeting
    {
        if (!$this->isReadyForMeeting($session)) {
            return $session->videoMeeting()->first();
        }

        try {
            return DB::transaction(function () use ($session) {
                $lockedSession = $session->newQuery()
                    ->whereKey($session->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$this->isReadyForMeeting($lockedSession)) {
                    return $lockedSession->videoMeeting()->first();
                }

                $existingMeeting = $lockedSession->videoMeeting()->first();

                if ($existingMeeting) {
                    return $existingMeeting;
                }

                $lockedSession->loadMissing(['pandit', 'panditService', 'service']);

                if (!$lockedSession->pandit) {
                    return null;
                }

                $startsAt = $this->startsAt($lockedSession);
                $duration = $this->durationMinutes($lockedSession, $startsAt);

                if (!$startsAt || !$duration) {
                    return null;
                }

                $meeting = $this->provider->createMeeting(
                    $lockedSession->pandit,
                    $this->topic($lockedSession),
                    $startsAt,
                    $duration
                );

                return $lockedSession->videoMeeting()->create([
                    'provider' => $meeting['provider'],
                    'external_meeting_id' => $meeting['external_meeting_id'],
                    'join_url' => $meeting['join_url'],
                    'host_url' => $meeting['host_url'],
                    'passcode' => $meeting['passcode'],
                    'status' => $meeting['status'],
                    'starts_at' => $startsAt,
                    'duration_minutes' => $duration,
                    'pandit_id' => $lockedSession->pandit_id,
                ]);
            });
        } catch (Throwable $exception) {
            Log::warning('Video meeting auto-create failed.', [
                'session_type' => $session::class,
                'session_id' => $session->getKey(),
                'provider' => $this->provider->providerName(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function isReadyForMeeting(Model $session): bool
    {
        return $session->status === 'confirmed'
            && $session->payment_status === 'paid'
            && ($session->booking_mode ?: 'online') === 'online'
            && filled($session->pandit_id);
    }

    private function startsAt(Model $session): ?CarbonInterface
    {
        if ($session->start_at) {
            return Carbon::parse($session->start_at)->setTimezone('Asia/Kolkata');
        }

        if ($session->booking_date && $session->slot_start_time) {
            return Carbon::parse(
                $session->booking_date->format('Y-m-d').' '.$session->slot_start_time,
                'Asia/Kolkata'
            );
        }

        if ($session->booking_date && $session->slot) {
            $slotTimes = $this->slotTimes($session->slot);

            if ($slotTimes) {
                return Carbon::parse(
                    $session->booking_date->format('Y-m-d').' '.$slotTimes['start'],
                    'Asia/Kolkata'
                );
            }
        }

        return null;
    }

    private function durationMinutes(Model $session, ?CarbonInterface $startsAt): ?int
    {
        if ($session->start_at && $session->end_at) {
            return (int) max(1, Carbon::parse($session->start_at)->diffInMinutes(Carbon::parse($session->end_at)));
        }

        if ($startsAt && $session->booking_date && $session->slot_end_time) {
            $endsAt = Carbon::parse(
                $session->booking_date->format('Y-m-d').' '.$session->slot_end_time,
                'Asia/Kolkata'
            );

            return (int) max(1, $startsAt->diffInMinutes($endsAt));
        }

        if ($session->booking_date && $session->slot) {
            $slotTimes = $this->slotTimes($session->slot);

            if ($slotTimes) {
                $slotStartsAt = Carbon::parse($session->booking_date->format('Y-m-d').' '.$slotTimes['start'], 'Asia/Kolkata');
                $slotEndsAt = Carbon::parse($session->booking_date->format('Y-m-d').' '.$slotTimes['end'], 'Asia/Kolkata');

                return (int) max(1, $slotStartsAt->diffInMinutes($slotEndsAt));
            }
        }

        if ($session->panditService?->duration_minutes) {
            return (int) $session->panditService->duration_minutes;
        }

        return null;
    }

    private function topic(Model $session): string
    {
        $type = $this->sessionKind($session);
        $meta = $this->bookingMeta($session);
        $name = $meta[$type.'_name'] ?? $session->service?->name ?? ucfirst($type).' Session';

        return 'BhaktiDeep '.ucfirst($type).': '.$name;
    }

    private function sessionKind(Model $session): string
    {
        if (filled($session->service_type)) {
            return $session->service_type;
        }

        return str_contains($session::class, 'Pooja') ? 'pooja' : 'hawan';
    }

    private function bookingMeta(Model $session): array
    {
        if (!$session->admin_note) {
            return [];
        }

        $meta = json_decode($session->admin_note, true);

        return is_array($meta) ? $meta : [];
    }

    private function slotTimes(string $slot): ?array
    {
        $parts = preg_split('/\s*-\s*/', trim($slot));

        if (count($parts) !== 2) {
            return null;
        }

        return [
            'start' => Carbon::parse($parts[0])->format('H:i:s'),
            'end' => Carbon::parse($parts[1])->format('H:i:s'),
        ];
    }
}
