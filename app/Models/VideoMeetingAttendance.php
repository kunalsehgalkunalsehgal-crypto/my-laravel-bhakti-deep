<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoMeetingAttendance extends Model
{
    public const PARTICIPANT_USER = 'user';
    public const PARTICIPANT_PANDIT = 'pandit';
    public const PARTICIPANT_UNKNOWN = 'unknown';

    public const EVENT_JOIN_ATTEMPT = 'join_attempt';
    public const EVENT_MEETING_STARTED = 'meeting_started';
    public const EVENT_MEETING_ENDED = 'meeting_ended';
    public const EVENT_PARTICIPANT_JOINED = 'participant_joined';
    public const EVENT_PARTICIPANT_LEFT = 'participant_left';

    protected $fillable = [
        'video_meeting_id',
        'session_type',
        'session_id',
        'participant_type',
        'participant_id',
        'zoom_participant_id',
        'provider',
        'event_type',
        'joined_at',
        'left_at',
        'duration',
        'provider_event_id',
        'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'duration' => 'integer',
            'provider_metadata' => 'array',
        ];
    }

    public static function recordJoinAttempt(
        Model $session,
        string $participantType,
        ?int $participantId,
        array $metadata = []
    ): self {
        $meeting = $session->relationLoaded('videoMeeting')
            ? $session->videoMeeting
            : $session->videoMeeting()->first();

        return static::create([
            'video_meeting_id' => $meeting?->id,
            'session_type' => $session::class,
            'session_id' => $session->getKey(),
            'participant_type' => $participantType,
            'participant_id' => $participantId,
            'provider' => $meeting?->provider ?: 'zoom',
            'event_type' => self::EVENT_JOIN_ATTEMPT,
            'joined_at' => now(),
            'provider_metadata' => $metadata,
        ]);
    }

    public function videoMeeting()
    {
        return $this->belongsTo(VideoMeeting::class);
    }

    public function session()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }
}
