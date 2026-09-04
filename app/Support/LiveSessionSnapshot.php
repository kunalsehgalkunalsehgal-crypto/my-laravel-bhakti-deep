<?php

namespace App\Support;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\LiveSessionInvite;
use App\Models\VideoMeetingAttendance;
use Illuminate\Database\Eloquent\Model;

class LiveSessionSnapshot
{
    public static function make(Model $booking): array
    {
        $booking->loadMissing(['videoMeetingAttendances', 'user', 'pandit']);
        $att = $booking->videoMeetingAttendances;
        $family = LiveSessionInvite::where('session_type', $booking::class)->where('session_id', $booking->id)->get();
        $started = $att->where('event_type', VideoMeetingAttendance::EVENT_MEETING_STARTED)->sortByDesc('joined_at')->first()?->joined_at;
        $ended = $att->where('event_type', VideoMeetingAttendance::EVENT_MEETING_ENDED)->sortByDesc('left_at')->first()?->left_at;
        $user = self::person($att, VideoMeetingAttendance::PARTICIPANT_USER, $booking->user_id);
        $pandit = self::person($att, VideoMeetingAttendance::PARTICIPANT_PANDIT, $booking->pandit_id);

        return [
            'session' => ['type' => self::type($booking), 'id' => $booking->id, 'status' => $ended ? 'Ended' : ($started ? 'Live' : 'Waiting'), 'started_at' => self::time($started), 'ended_at' => self::time($ended)],
            'people' => ['user' => $user, 'pandit' => $pandit],
            'family' => $family->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'relation' => $i->relation, 'status' => $i->left_at ? 'Left' : ($i->joined_at ? 'Present' : 'Not Joined'), 'joined_at' => self::time($i->joined_at), 'left_at' => self::time($i->left_at)])->values(),
            'counts' => [
                'total_present' => collect([$user, $pandit])->where('status', 'Present')->count() + $family->whereNotNull('joined_at')->whereNull('left_at')->count(),
                'joined' => $att->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED)->count() + $family->whereNotNull('joined_at')->count(),
                'left' => $att->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT)->count() + $family->whereNotNull('left_at')->count(),
                'family_present' => $family->whereNotNull('joined_at')->whereNull('left_at')->count(),
            ],
        ];
    }

    private static function person($att, string $type, ?int $id): array
    {
        $rows = $att->where('participant_type', $type)->where('participant_id', $id);
        $join = $rows->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED)->sortByDesc('joined_at')->first();
        $left = $rows->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT)->sortByDesc('left_at')->first();
        $status = $join ? (($left?->left_at && $left->left_at->gt($join->joined_at)) ? 'Left' : 'Present') : ($left ? 'Left' : 'Not Joined');

        return ['status' => $status, 'joined_at' => self::time($join?->joined_at), 'left_at' => self::time($left?->left_at)];
    }

    private static function type(Model $booking): string
    {
        return $booking instanceof HawanSession ? 'hawan' : ($booking instanceof PoojaSession ? 'pooja' : 'unknown');
    }

    private static function time($time): ?string
    {
        return $time?->format('d M Y, h:i A');
    }
}
