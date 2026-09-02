<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Services\PanditBookingService;
use App\Services\VideoMeetingProviderManager;
use App\Models\VideoMeetingAttendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class VideoMeetingSdkController extends Controller
{
    public function config(
        Request $request,
        string $type,
        string $id,
        PanditBookingService $bookingService,
        VideoMeetingProviderManager $providers
    ): JsonResponse {
        $bookingRecord = $this->bookingRecord($type, $id);

        abort_unless(
            $bookingService->canAccessPrivateSession($bookingRecord, $request),
            403
        );

        abort_unless(
            $bookingRecord->payment_status === 'paid'
            && $bookingRecord->status === 'confirmed'
            && $bookingRecord->videoMeeting
            && filled($bookingRecord->videoMeeting->external_meeting_id),
            403
        );

        $isAssignedPandit = Auth::guard('pandit')->check()
            && (int) Auth::guard('pandit')->id() === (int) $bookingRecord->pandit_id;
        $isHost = $request->query('mode') === 'host';

        abort_if($isHost && !$isAssignedPandit, 403);

        $participantType = VideoMeetingAttendance::PARTICIPANT_UNKNOWN;
        $participantId = null;

        if ($isAssignedPandit) {
            $participantType = VideoMeetingAttendance::PARTICIPANT_PANDIT;
            $participantId = Auth::guard('pandit')->id();
        } elseif (Auth::check() && (int) Auth::id() === (int) $bookingRecord->user_id) {
            $participantType = VideoMeetingAttendance::PARTICIPANT_USER;
            $participantId = Auth::id();
        }

        VideoMeetingAttendance::recordJoinAttempt($bookingRecord, $participantType, $participantId, [
            'source' => 'embedded_sdk_config',
            'route' => 'live.session.sdk',
            'mode' => $isHost ? 'host' : 'participant',
        ]);

        try {
            $payload = $providers
                ->for($bookingRecord->videoMeeting->provider)
                ->embeddedMeetingConfig(
                    $bookingRecord->videoMeeting,
                    $isHost,
                    $this->displayName($bookingRecord, $isHost),
                    $this->displayEmail($bookingRecord, $isHost)
                );
        } catch (Throwable $exception) {
            Log::warning('Unable to prepare embedded video meeting.', [
                'provider' => $bookingRecord->videoMeeting->provider,
                'session_type' => $type,
                'session_id' => $bookingRecord->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Embedded meeting is unavailable. Please use the external meeting link.',
            ], 422);
        }

        return response()->json($payload);
    }

    private function bookingRecord(string $type, string $id): Model
    {
        return match ($type) {
            'pooja' => PoojaSession::with(['sankalp', 'user', 'pandit', 'videoMeeting'])->findOrFail($id),
            'hawan' => HawanSession::with(['sankalp', 'user', 'pandit', 'videoMeeting'])->findOrFail($id),
        };
    }

    private function displayName(Model $bookingRecord, bool $isHost): string
    {
        if ($isHost) {
            return $bookingRecord->pandit?->pandit_name
                ?: $bookingRecord->pandit?->full_name
                ?: 'Pandit';
        }

        return Auth::user()?->name
            ?: $bookingRecord->sankalp?->full_name
            ?: 'BhaktiDeep Guest';
    }

    private function displayEmail(Model $bookingRecord, bool $isHost): ?string
    {
        if ($isHost) {
            return $bookingRecord->pandit?->email;
        }

        return Auth::user()?->email;
    }
}
