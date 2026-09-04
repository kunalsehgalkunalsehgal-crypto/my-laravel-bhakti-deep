<?php

namespace App\Http\Controllers;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\LiveSessionInvite;
use App\Models\VideoMeetingAttendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PanditLiveSessionController extends Controller
{
    public function index(): View
    {
        $pandit = Auth::guard('pandit')->user();
        abort_unless($pandit, 403);

        $hawanSessions = HawanSession::with(['sankalp', 'user', 'service', 'videoMeeting'])
            ->where('pandit_id', $pandit->id)
            ->where('payment_status', 'paid')
            ->where('status', 'confirmed')
            ->whereHas('videoMeeting')
            ->latest()
            ->get()
            ->map(fn (Model $booking) => $this->liveSessionRow($booking, 'hawan'));

        $poojaSessions = PoojaSession::with(['sankalp', 'user', 'service', 'videoMeeting'])
            ->where('pandit_id', $pandit->id)
            ->where('payment_status', 'paid')
            ->where('status', 'confirmed')
            ->whereHas('videoMeeting')
            ->latest()
            ->get()
            ->map(fn (Model $booking) => $this->liveSessionRow($booking, 'pooja'));

        $liveSessions = collect($hawanSessions->all())
            ->merge($poojaSessions->all())
            ->sortBy([
                ['booking_date', 'asc'],
                ['slot', 'asc'],
            ])
            ->values();

        return view('pandit.live-sessions', compact('pandit', 'liveSessions'));
    }

    public function show(string $type, string $id): View
    {
        $pandit = Auth::guard('pandit')->user();
        abort_unless($pandit, 403);

        $booking = $this->bookingRecord($type, $id);

        abort_unless((int) $booking->pandit_id === (int) $pandit->id, 403);

        $booking->load([
            'sankalp',
            'user',
            'pandit',
            'service',
            'videoMeeting',
            'videoMeetingAttendances',
        ]);

        $serviceName = $this->serviceName($booking, $type);
        $sessionProgress = $this->sessionProgress($booking);
        $presenceRows = $this->presenceRows($booking);
        $canStartProviderMeeting = $this->bookingReady($booking);

        return view('pandit.live-session', [
            'pandit' => $pandit,
            'sessionType' => $type,
            'sessionId' => $booking->id,
            'bookingRecord' => $booking,
            'serviceName' => $serviceName,
            'sessionProgress' => $sessionProgress,
            'presenceRows' => $presenceRows,
            'embeddedMeetingView' => $booking->videoMeeting?->provider === 'zoom' ? 'video-meetings.zoom-sdk' : null,
            'canStartProviderMeeting' => $canStartProviderMeeting,
            'providerMeetingActionUrl' => $canStartProviderMeeting
                ? route('live.session.start', ['type' => $type, 'id' => $booking->id])
                : null,
            'providerMeetingAction' => 'Start '.Str::headline($type),
            'sdkEndpointUrl' => $canStartProviderMeeting
                ? route('live.session.sdk', ['type' => $type, 'id' => $booking->id, 'mode' => 'host'])
                : null,
        ]);
    }

    private function bookingRecord(string $type, string $id): Model
    {
        return match ($type) {
            'hawan' => HawanSession::findOrFail($id),
            'pooja' => PoojaSession::findOrFail($id),
        };
    }

    private function serviceName(Model $booking, string $type): string
    {
        $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];

        return $meta[$type.'_name']
            ?? $booking->service?->name
            ?? Str::headline($type).' Booking';
    }

    private function liveSessionRow(Model $booking, string $type): array
    {
        return [
            'id' => $booking->id,
            'type' => $type,
            'label' => Str::headline($type),
            'booking_id' => Str::upper($type).'-'.$booking->id,
            'service_name' => $this->serviceName($booking, $type),
            'yajman' => $booking->sankalp?->full_name ?: $booking->user?->name ?: 'Main Devotee',
            'booking_date' => $booking->booking_date,
            'slot' => $booking->slot,
            'status' => $booking->status,
            'url' => route('pandit.live-sessions.show', ['type' => $type, 'id' => $booking->id]),
        ];
    }

    private function sessionProgress(Model $booking): array
    {
        $attendances = $booking->videoMeetingAttendances;
        $started = $attendances
            ->where('event_type', VideoMeetingAttendance::EVENT_MEETING_STARTED)
            ->sortByDesc('joined_at')
            ->first();
        $ended = $attendances
            ->where('event_type', VideoMeetingAttendance::EVENT_MEETING_ENDED)
            ->sortByDesc('left_at')
            ->first();

        $status = match (true) {
            filled($ended) => 'Ended',
            filled($started) => 'Live',
            default => 'Waiting to Start',
        };

        return [
            'status' => $status,
            'booking_status' => ucfirst(str_replace('_', ' ', $booking->status ?: 'pending')),
            'meeting_status' => ucfirst(str_replace('_', ' ', $booking->videoMeeting?->status ?: 'pending')),
            'started_at' => $started?->joined_at,
            'ended_at' => $ended?->left_at,
            'actual_joined_count' => $attendances
                ->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED)
                ->count(),
            'actual_left_count' => $attendances
                ->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT)
                ->count(),
        ];
    }

    private function presenceRows(Model $booking): array
    {
        $rows = [
            $this->devoteePresenceRow($booking),
            $this->panditPresenceRow($booking),
        ];

        $familyInvites = LiveSessionInvite::query()
            ->where('session_type', $booking::class)
            ->where('session_id', $booking->id)
            ->oldest()
            ->get();

        foreach ($familyInvites as $invite) {
            $rows[] = [
                'id' => $invite->id,
                'name' => $invite->name,
                'role' => 'Family - '.$invite->relation,
                'status' => $this->inviteStatus($invite),
                'joined_at' => $invite->joined_at,
                'left_at' => $invite->left_at,
            ];
        }

        return $rows;
    }

    private function devoteePresenceRow(Model $booking): array
    {
        $attendances = $booking->videoMeetingAttendances
            ->where('participant_type', VideoMeetingAttendance::PARTICIPANT_USER)
            ->where('participant_id', $booking->user_id);

        $latestJoin = $attendances
            ->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED)
            ->sortByDesc('joined_at')
            ->first();
        $latestLeft = $attendances
            ->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT)
            ->sortByDesc('left_at')
            ->first();

        $status = 'Not Joined';

        if ($latestJoin) {
            $status = $latestLeft && $latestLeft->left_at && $latestLeft->left_at->greaterThan($latestJoin->joined_at)
                ? 'Left'
                : 'Present';
        }

        return [
            'name' => $booking->sankalp?->full_name ?: $booking->user?->name ?: 'Main Devotee',
            'role' => 'Main Devotee',
            'status' => $status,
            'joined_at' => $latestJoin?->joined_at,
            'left_at' => $latestLeft?->left_at,
        ];
    }

    private function panditPresenceRow(Model $booking): array
    {
        $attendances = $booking->videoMeetingAttendances
            ->where('participant_type', VideoMeetingAttendance::PARTICIPANT_PANDIT)
            ->where('participant_id', $booking->pandit_id);
        $latestJoin = $attendances->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED)->sortByDesc('joined_at')->first();
        $latestLeft = $attendances->where('event_type', VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT)->sortByDesc('left_at')->first();
        $status = $latestJoin ? (($latestLeft?->left_at && $latestLeft->left_at->greaterThan($latestJoin->joined_at)) ? 'Left' : 'Present') : ($latestLeft ? 'Left' : 'Not Joined');

        return [
            'name' => $booking->pandit?->pandit_name ?: ($booking->pandit?->full_name ?: 'Pandit'),
            'role' => 'Pandit',
            'status' => $status,
            'joined_at' => $latestJoin?->joined_at,
            'left_at' => $latestLeft?->left_at,
        ];
    }

    private function inviteStatus(LiveSessionInvite $invite): string
    {
        if ($invite->left_at) {
            return 'Left';
        }

        if ($invite->joined_at) {
            return 'Present';
        }

        return 'Not Joined';
    }

    private function bookingReady(Model $booking): bool
    {
        return $booking->payment_status === 'paid'
            && $booking->status === 'confirmed'
            && $booking->videoMeeting
            && filled($booking->videoMeeting->external_meeting_id);
    }
}
