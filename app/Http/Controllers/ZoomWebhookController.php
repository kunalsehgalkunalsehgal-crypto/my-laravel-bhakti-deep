<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\PanditZoomConnection;
use App\Models\VideoMeeting;
use App\Models\VideoMeetingAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZoomWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (!$this->hasValidSignature($request)) {
            Log::warning('Rejected unsigned Zoom webhook.', [
                'event' => $request->input('event'),
            ]);

            return response()->json(['message' => 'Invalid webhook signature.'], 403);
        }

        // Zoom endpoint validation
        if ($request->input('event') === 'endpoint.url_validation') {

            $plainToken = $request->input('payload.plainToken');

            $encryptedToken = hash_hmac(
                'sha256',
                $plainToken,
                config('services.zoom.webhook_secret_token')
            );

            return response()->json([
                'plainToken' => $plainToken,
                'encryptedToken' => $encryptedToken,
            ]);
        }

        $eventType = $this->attendanceEventType((string) $request->input('event'));

        if (!$eventType) {
            Log::info('Ignored Zoom webhook event.', [
                'event' => $request->input('event'),
            ]);

            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $meeting = $this->meetingFromWebhook($request);

        if (!$meeting) {
            Log::info('Zoom webhook meeting not matched.', [
                'event' => $request->input('event'),
                'meeting_id' => $request->input('payload.object.id'),
            ]);

            return response()->json([
                'status' => 'ignored',
            ]);
        }

        $this->storeAttendance($request, $meeting, $eventType);

        return response()->json([
            'status' => 'success'
        ]);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = (string) config('services.zoom.webhook_secret_token');
        $timestamp = (string) $request->header('x-zm-request-timestamp', '');
        $signature = (string) $request->header('x-zm-signature', '');

        if ($secret === '' || $timestamp === '' || $signature === '') {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $message = 'v0:'.$timestamp.':'.$request->getContent();
        $expected = 'v0='.hash_hmac('sha256', $message, $secret);

        return hash_equals($expected, $signature);
    }

    private function attendanceEventType(string $zoomEvent): ?string
    {
        return [
            'meeting.started' => VideoMeetingAttendance::EVENT_MEETING_STARTED,
            'meeting.ended' => VideoMeetingAttendance::EVENT_MEETING_ENDED,
            'meeting.participant_joined' => VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED,
            'meeting.participant_left' => VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT,
        ][$zoomEvent] ?? null;
    }

    private function meetingFromWebhook(Request $request): ?VideoMeeting
    {
        $meetingId = (string) $request->input('payload.object.id', '');

        if ($meetingId === '') {
            return null;
        }

        return VideoMeeting::query()
            ->with('pandit')
            ->where('provider', 'zoom')
            ->where('external_meeting_id', $meetingId)
            ->whereIn('session_type', [HawanSession::class, PoojaSession::class])
            ->first();
    }

    private function storeAttendance(Request $request, VideoMeeting $meeting, string $eventType): void
    {
        $participant = (array) $request->input('payload.object.participant', []);
        [$participantType, $participantId] = $this->knownParticipant($meeting, $participant);
        $zoomParticipantId = $this->zoomParticipantId($participant);
        $joinedAt = $this->joinedAt($request, $eventType, $participant);
        $leftAt = $this->leftAt($request, $eventType, $participant);
        $duration = $this->duration($participant);
        $providerEventId = $this->providerEventId($request, $meeting, $eventType, $zoomParticipantId, $joinedAt, $leftAt, $duration);

        VideoMeetingAttendance::updateOrCreate(
            ['provider_event_id' => $providerEventId],
            [
                'video_meeting_id' => $meeting->id,
                'session_type' => $meeting->session_type,
                'session_id' => $meeting->session_id,
                'participant_type' => $participantType,
                'participant_id' => $participantId,
                'zoom_participant_id' => $zoomParticipantId,
                'provider' => 'zoom',
                'event_type' => $eventType,
                'joined_at' => $joinedAt,
                'left_at' => $leftAt,
                'duration' => $duration,
                'provider_metadata' => $this->safeMetadata($request),
            ]
        );
    }

    private function knownParticipant(VideoMeeting $meeting, array $participant): array
    {
        if (!$participant) {
            return [VideoMeetingAttendance::PARTICIPANT_UNKNOWN, null];
        }

        $zoomParticipantId = $this->zoomParticipantId($participant);
        $participantEmail = Str::lower((string) ($participant['email'] ?? $participant['user_email'] ?? ''));
        $booking = $meeting->session;
        $booking?->loadMissing(['user', 'pandit']);

        $panditConnection = $meeting->pandit_id
            ? PanditZoomConnection::where('pandit_id', $meeting->pandit_id)->first()
            : null;
        $panditEmail = Str::lower((string) ($meeting->pandit?->email ?? $booking?->pandit?->email ?? ''));
        $panditZoomEmail = Str::lower((string) ($panditConnection?->zoom_email ?? ''));
        $panditZoomUserId = (string) ($panditConnection?->zoom_user_id ?? '');

        if ($meeting->pandit_id && (
            ($zoomParticipantId && $panditZoomUserId !== '' && hash_equals($panditZoomUserId, $zoomParticipantId))
            || ($participantEmail !== '' && in_array($participantEmail, array_filter([$panditEmail, $panditZoomEmail]), true))
        )) {
            return [VideoMeetingAttendance::PARTICIPANT_PANDIT, (int) $meeting->pandit_id];
        }

        $bookingUserEmail = Str::lower((string) ($booking?->user?->email ?? ''));

        if ($booking?->user_id && $participantEmail !== '' && $bookingUserEmail !== '' && hash_equals($bookingUserEmail, $participantEmail)) {
            return [VideoMeetingAttendance::PARTICIPANT_USER, (int) $booking->user_id];
        }

        return [VideoMeetingAttendance::PARTICIPANT_UNKNOWN, null];
    }

    private function zoomParticipantId(array $participant): ?string
    {
        $id = (string) ($participant['participant_user_id'] ?? $participant['user_id'] ?? $participant['id'] ?? $participant['participant_id'] ?? '');

        return $id !== '' ? $id : null;
    }

    private function joinedAt(Request $request, string $eventType, array $participant): ?Carbon
    {
        if ($eventType === VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED) {
            return $this->timeFrom($participant['join_time'] ?? $request->input('payload.object.start_time'));
        }

        if ($eventType === VideoMeetingAttendance::EVENT_MEETING_STARTED) {
            return $this->timeFrom($request->input('payload.object.start_time'));
        }

        return $this->timeFrom($participant['join_time'] ?? null);
    }

    private function leftAt(Request $request, string $eventType, array $participant): ?Carbon
    {
        if ($eventType === VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT) {
            return $this->timeFrom($participant['leave_time'] ?? $request->input('payload.object.end_time'));
        }

        if ($eventType === VideoMeetingAttendance::EVENT_MEETING_ENDED) {
            return $this->timeFrom($request->input('payload.object.end_time'));
        }

        return null;
    }

    private function timeFrom($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function duration(array $participant): ?int
    {
        $duration = $participant['duration'] ?? $participant['participant_duration'] ?? null;

        return is_numeric($duration) ? (int) $duration : null;
    }

    private function providerEventId(
        Request $request,
        VideoMeeting $meeting,
        string $eventType,
        ?string $zoomParticipantId,
        ?Carbon $joinedAt,
        ?Carbon $leftAt,
        ?int $duration
    ): string {
        return hash('sha256', json_encode([
            'provider' => 'zoom',
            'event' => $request->input('event'),
            'event_ts' => $request->input('event_ts'),
            'meeting_id' => $meeting->external_meeting_id,
            'meeting_uuid' => $request->input('payload.object.uuid'),
            'event_type' => $eventType,
            'zoom_participant_id' => $zoomParticipantId,
            'joined_at' => $joinedAt?->toIso8601String(),
            'left_at' => $leftAt?->toIso8601String(),
            'duration' => $duration,
        ]));
    }

    private function safeMetadata(Request $request): array
    {
        return [
            'event' => $request->input('event'),
            'event_ts' => $request->input('event_ts'),
            'account_id' => $request->input('payload.account_id'),
            'object' => [
                'id' => $request->input('payload.object.id'),
                'uuid' => $request->input('payload.object.uuid'),
                'host_id' => $request->input('payload.object.host_id'),
                'topic' => $request->input('payload.object.topic'),
                'type' => $request->input('payload.object.type'),
                'start_time' => $request->input('payload.object.start_time'),
                'end_time' => $request->input('payload.object.end_time'),
            ],
            'participant' => $request->input('payload.object.participant'),
        ];
    }
}
