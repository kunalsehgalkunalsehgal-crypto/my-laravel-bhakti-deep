<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Services\PanditBookingService;
use App\Services\VideoMeetingProviderManager;
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
