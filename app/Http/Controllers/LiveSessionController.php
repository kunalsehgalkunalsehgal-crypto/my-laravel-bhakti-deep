<?php

namespace App\Http\Controllers;

use App\Events\FamilyMemberStatusChanged;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\PoojaSession;
use App\Models\LiveSessionInvite;
use App\Services\VideoMeetingProviderManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    public function storeInvite(Request $request, string $type, string $id): JsonResponse
    {
        $booking = $this->ownedBooking($type, $id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'max:100'],
        ]);
        $token = Str::random(64);

        $invite = LiveSessionInvite::create([
            'session_type' => $booking::class,
            'session_id' => $booking->id,
            'name' => $validated['name'],
            'relation' => $validated['relation'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => $this->inviteExpiry($booking),
        ]);

        return response()->json([
            'success' => true,
            'invite' => $this->invitePayload($invite, $token),
        ]);
    }

    public function revokeInvite(string $type, string $id, LiveSessionInvite $invite): JsonResponse
    {
        $booking = $this->ownedBooking($type, $id);

        abort_unless($invite->session_type === $booking::class && (int) $invite->session_id === (int) $booking->id, 404);

        $invite->update(['revoked_at' => now()]);

        return response()->json([
            'success' => true,
            'status' => 'Revoked',
        ]);
    }

    public function joinInvite(string $token): View
    {
        $invite = $this->validInvite($token);
        $this->markInviteJoined($invite);

        $booking = $invite->booking;
        abort_unless($booking, 404);
        $booking->load(['sankalp', 'user', 'pandit', 'videoMeeting']);
        $type = $booking instanceof HawanSession ? 'hawan' : 'pooja';

        return view('pages.live', [
            'sessionType' => $type,
            'sessionId' => $booking->id,
            'bookingRecord' => $booking,
            'embeddedMeetingView' => $booking->videoMeeting?->provider === 'zoom' ? 'video-meetings.zoom-sdk' : null,
            'familyInvites' => $this->familyInvites($booking),
            'activeFamilyInvite' => $invite,
            'sdkEndpointUrl' => route('live.family.sdk', ['token' => $token]),
            'providerMeetingActionUrl' => $booking->videoMeeting?->join_url,
            'providerMeetingAction' => 'Join '.ucfirst($type),
            'canManageFamily' => false,
        ]);
    }

    public function leaveInvite(string $token): JsonResponse
    {
        $invite = LiveSessionInvite::where('token_hash', hash('sha256', $token))->firstOrFail();

        if ($invite->isValid()) {
            $this->markInviteLeft($invite);
        }

        return response()->json(['success' => true]);
    }

    public function inviteSdkConfig(string $token, VideoMeetingProviderManager $providers): JsonResponse
    {
        $invite = $this->validInvite($token);
        $booking = $invite->booking;
        abort_unless($booking, 404);
        $booking->load('videoMeeting');

        abort_unless($this->bookingReady($booking), 403);

        $this->markInviteJoined($invite);

        return response()->json(
            $providers
                ->for($booking->videoMeeting->provider)
                ->embeddedMeetingConfig($booking->videoMeeting, false, $invite->name, null)
        );
    }

    public function payDakshina(Request $request, string $type, string $id): JsonResponse
    {
        $booking = $this->ownedBooking($type, $id);
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
        $amount = (int) $validated['amount'];

        $donation = Donation::create([
            'user_id' => Auth::id(),
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => Auth::user()?->name ?: $booking->sankalp?->full_name,
            'donor_email' => Auth::user()?->email,
            'donor_mobile' => Auth::user()?->mobile ?: $booking->sankalp?->mobile,
            'razorpay_order_id' => 'demo_dakshina_order_'.$type.'_'.$booking->id.'_'.Str::random(8),
            'razorpay_payment_id' => 'demo_dakshina_payment_'.$type.'_'.$booking->id.'_'.Str::random(8),
            'payment_status' => 'paid',
            'receipt_number' => 'BDD-DAK-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'paid_at' => now(),
        ]);

        PaymentLog::create([
            'donation_id' => $donation->id,
            'user_id' => Auth::id(),
            'gateway' => 'demo',
            'order_id' => $donation->razorpay_order_id,
            'payment_id' => $donation->razorpay_payment_id,
            'status' => 'paid',
            'amount' => $amount,
            'payload' => [
                'booking_type' => $type,
                'session_type' => $booking::class,
                'session_id' => $booking->id,
                'server_verified' => true,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dakshina paid successfully.',
            'amount' => $amount,
            'receipt_number' => $donation->receipt_number,
        ]);
    }

    public function familyInvites(Model $booking)
    {
        return LiveSessionInvite::query()
            ->where('session_type', $booking::class)
            ->where('session_id', $booking->id)
            ->latest()
            ->get();
    }

    private function ownedBooking(string $type, string $id): Model
    {
        abort_unless(Auth::check(), 403);

        $booking = $this->bookingRecord($type, $id);

        abort_unless((int) $booking->user_id === (int) Auth::id(), 403);
        abort_unless($this->bookingReady($booking), 403);

        return $booking;
    }

    private function bookingRecord(string $type, string $id): Model
    {
        return match ($type) {
            'pooja' => PoojaSession::with(['sankalp', 'videoMeeting'])->findOrFail($id),
            'hawan' => HawanSession::with(['sankalp', 'videoMeeting'])->findOrFail($id),
        };
    }

    private function validInvite(string $token): LiveSessionInvite
    {
        $invite = LiveSessionInvite::where('token_hash', hash('sha256', $token))->firstOrFail();

        abort_unless($invite->isValid(), 403);

        return $invite;
    }

    private function bookingReady(Model $booking): bool
    {
        return $booking->payment_status === 'paid'
            && $booking->status === 'confirmed'
            && $booking->videoMeeting
            && filled($booking->videoMeeting->external_meeting_id);
    }

    private function inviteExpiry(Model $booking)
    {
        if ($booking->videoMeeting?->starts_at) {
            return $booking->videoMeeting->starts_at
                ->copy()
                ->addMinutes((int) ($booking->videoMeeting->duration_minutes ?: 60))
                ->addHours(2);
        }

        return now()->addDays(7);
    }

    private function invitePayload(LiveSessionInvite $invite, ?string $token = null): array
    {
        return [
            'id' => $invite->id,
            'name' => $invite->name,
            'relation' => $invite->relation,
            'status' => $invite->statusLabel(),
            'expires_at' => $invite->expires_at?->format('d M Y, h:i A'),
            'join_url' => $token ? route('live.family.join', ['token' => $token]) : null,
            'revoke_url' => route('live.family.revoke', [
                'type' => $invite->session_type === HawanSession::class ? 'hawan' : 'pooja',
                'id' => $invite->session_id,
                'invite' => $invite,
            ]),
        ];
    }

    private function markInviteJoined(LiveSessionInvite $invite): void
    {
        $oldStatus = $invite->statusLabel();

        $invite->update([
            'joined_at' => $invite->joined_at ?: now(),
            'left_at' => null,
            'last_seen_at' => now(),
        ]);

        $this->broadcastInviteStatus($invite, $oldStatus);
    }

    private function markInviteLeft(LiveSessionInvite $invite): void
    {
        $oldStatus = $invite->statusLabel();

        $invite->update([
            'left_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->broadcastInviteStatus($invite, $oldStatus);
    }

    private function broadcastInviteStatus(LiveSessionInvite $invite, string $oldStatus): void
    {
        if ($invite->statusLabel() === $oldStatus) {
            return;
        }

        broadcast(new FamilyMemberStatusChanged($invite, $this->joinedInviteCount($invite)));
    }

    private function joinedInviteCount(LiveSessionInvite $invite): int
    {
        return LiveSessionInvite::query()
            ->where('session_type', $invite->session_type)
            ->where('session_id', $invite->session_id)
            ->whereNotNull('joined_at')
            ->whereNull('left_at')
            ->count();
    }
}
