<?php

namespace App\Http\Controllers;
use Illuminate\View\View;
use Illuminate\Http\Response;
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
                if ($participantType === VideoMeetingAttendance::PARTICIPANT_PANDIT) {
    $payload['customerKey'] = 'p-'.$participantId.'-s-'.$bookingRecord->id;
} elseif ($participantType === VideoMeetingAttendance::PARTICIPANT_USER) {
    $payload['customerKey'] = 'u-'.$participantId.'-s-'.$bookingRecord->id;
} else {
    $payload['customerKey'] = 'g-s-'.$bookingRecord->id;
}
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



public function clientView(
    Request $request,
    string $type,
    string $id,
    PanditBookingService $bookingService
): View {
    $bookingRecord = $this->bookingRecord($type, $id);

    abort_unless(
        $bookingService->canAccessPrivateSession(
            $bookingRecord,
            $request
        ),
        403
    );

    abort_unless(
        $bookingRecord->payment_status === 'paid'
        && $bookingRecord->status === 'confirmed'
        && $bookingRecord->videoMeeting
        && filled(
            $bookingRecord->videoMeeting->external_meeting_id
        ),
        403
    );

    $isAssignedPandit =
        Auth::guard('pandit')->check()
        && (int) Auth::guard('pandit')->id()
            === (int) $bookingRecord->pandit_id;

    $isHost =
        $request->query('mode') === 'host';

    /*
     * Koi normal user host mode
     * force nahi kar sakta.
     */
    abort_if(
        $isHost && !$isAssignedPandit,
        403
    );


    $params = [
        'type' => $type,
        'id' => $bookingRecord->id,

        'mode' => $isHost
            ? 'host'
            : 'participant',
    ];


    /*
     * Agar existing private-session token
     * use ho raha hai to preserve karo.
     */
    if ($request->filled('token')) {
        $params['token'] =
            $request->query('token');
    }


    return view(
        'video-meetings.zoom-client',
        [
            /*
             * Ye existing config()
             * method hi use karega.
             */
            'sdkEndpointUrl' =>
                route(
                    'live.session.sdk',
                    $params
                ),

            'leaveUrl' =>
                route(
                    'live.session.client.exit'
                ),

            'zoomSdkVersion' =>
                config(
                    'video_meetings.providers.zoom.meeting_sdk_cdn_version'
                ),
        ]
    );
}


public function clientExit(): Response
{
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            background: #111;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        p {
            color: #aaa;
        }
    </style>
</head>

<body>

<div>
    <h3>You left the meeting</h3>
    <p>Returning to BhaktiDeep...</p>
</div>

<script>
    try {
        window.parent.postMessage(
            {
                type: 'bhaktideep:zoom-left'
            },
            window.location.origin
        );
    } catch (error) {
        console.warn(error);
    }
</script>

</body>
</html>
HTML;

    return response(
        $html,
        200,
        [
            'Content-Type' =>
                'text/html; charset=UTF-8'
        ]
    );
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
