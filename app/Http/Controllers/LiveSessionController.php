<?php

namespace App\Http\Controllers;

use App\Events\FamilyMemberStatusChanged;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\PoojaSession;
use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\LiveSessionInvite;
use App\Models\SessionCompletionProof;
use App\Models\Pandit\PanditNotification;
use App\Models\VideoMeetingAttendance;
use App\Services\PanditPayoutLedgerService;
use App\Services\UserBookingNotificationService;
use App\Services\VideoMeetingProviderManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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



public function inviteClientView(
    string $token
): View {

    $invite =
        $this->validInvite($token);

    $booking =
        $invite->booking;


    abort_unless(
        $booking,
        404
    );


    $booking->load(
        'videoMeeting'
    );


    abort_unless(
        $this->bookingReady($booking),
        403
    );


    return view(
        'video-meetings.zoom-client',
        [

            /*
             * Family ke liye existing
             * family SDK endpoint.
             */
            'sdkEndpointUrl' =>
                route(
                    'live.family.sdk',
                    [
                        'token' =>
                            $token
                    ]
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

        VideoMeetingAttendance::recordJoinAttempt($booking, VideoMeetingAttendance::PARTICIPANT_UNKNOWN, null, [
            'source' => 'family_invite_sdk_config',
            'route' => 'live.family.sdk',
            'invite_id' => $invite->id,
        ]);

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

    public function storeIssueReport(Request $request, string $type, string $id): RedirectResponse
    {
        $booking = $this->ownedReportableBooking($type, $id);
        $activeStatuses = [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW];

        if ($booking->userConfirmations()
            ->where('user_id', Auth::id())
            ->whereIn('status', [BookingUserConfirmation::STATUS_CONFIRMED, BookingUserConfirmation::STATUS_AUTO_CONFIRMED])
            ->whereNotNull('confirmed_at')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'dispute' => 'A dispute cannot be opened after completion has been confirmed.',
            ]);
        }

        $existingDispute = $booking->disputes()
            ->where('user_id', Auth::id())
            ->whereIn('status', $activeStatuses)
            ->first();

        if ($existingDispute) {
            $this->markCompletionDisputed($booking);
            app(PanditPayoutLedgerService::class)->syncForBooking($booking, 'existing_dispute_open');

            return back()->with('success', 'Issue Reported - Status: Open');
        }

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                Rule::in([
                    'pandit_not_joined',
                    'pandit_joined_late',
                    'session_incomplete',
                    'wrong_service',
                    'technical_issue',
                    'behaviour_issue',
                    'other',
                ]),
            ],
            'description' => ['required', 'string', 'max:5000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $dispute = $booking->disputes()->create([
            'user_id' => Auth::id(),
            'pandit_id' => $booking->pandit_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filePath = Storage::disk('local')->putFileAs(
                'dispute-evidences/'.$dispute->id,
                $file,
                Str::uuid().'.'.$file->extension()
            );

            $dispute->evidences()->create([
                'uploaded_by_type' => Auth::user()::class,
                'uploaded_by_id' => Auth::id(),
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $this->notifyPanditAboutIssueReport($booking, $dispute);
        app(UserBookingNotificationService::class)->reportSubmitted($booking, $dispute);
        $this->markCompletionDisputed($booking);
        app(PanditPayoutLedgerService::class)->syncForBooking($booking, 'dispute_opened');

        return back()->with('success', 'Issue Reported - Status: Open');
    }

    public function confirmCompletion(string $type, string $id): RedirectResponse
    {
        $booking = $this->ownedReportableBooking($type, $id);

        abort_unless($booking->status === 'completed' && $booking->payment_status === 'paid', 403);
        abort_unless($booking->completionProofs()->whereNotNull('file_path')->where('status', '!=', SessionCompletionProof::STATUS_REJECTED)->exists(), 403);

        if ($booking->disputes()->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW])->exists()) {
            throw ValidationException::withMessages(['completion' => 'Resolve the open dispute before confirming completion.']);
        }

        BookingUserConfirmation::updateOrCreate(
            [
                'session_type' => $booking::class,
                'session_id' => $booking->id,
                'user_id' => Auth::id(),
            ],
            [
                'status' => BookingUserConfirmation::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'metadata' => ['confirmed_by' => 'user'],
            ]
        );

        app(PanditPayoutLedgerService::class)->syncForBooking($booking, 'user_confirmed_completion');

        return back()->with('success', 'Completion confirmed successfully.');
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

    private function ownedReportableBooking(string $type, string $id): Model
    {
        abort_unless(Auth::check(), 403);

        $booking = $this->bookingRecord($type, $id);

        abort_unless((int) $booking->user_id === (int) Auth::id(), 403);

        return $booking;
    }

    private function bookingRecord(string $type, string $id): Model
    {
        return match ($type) {
            'pooja' => PoojaSession::with(['sankalp', 'videoMeeting', 'completionProofs', 'userConfirmations'])->findOrFail($id),
            'hawan' => HawanSession::with(['sankalp', 'videoMeeting', 'completionProofs', 'userConfirmations'])->findOrFail($id),
        };
    }

    private function markCompletionDisputed(Model $booking): void
    {
        $booking->userConfirmations()
            ->where('user_id', Auth::id())
            ->where('status', BookingUserConfirmation::STATUS_PENDING)
            ->update([
                'status' => BookingUserConfirmation::STATUS_DISPUTED,
                'disputed_at' => now(),
            ]);
    }

    private function notifyPanditAboutIssueReport(Model $booking, Dispute $dispute): void
    {
        if (!$booking->pandit_id) {
            return;
        }

        $type = $booking instanceof HawanSession ? 'Hawan' : 'Pooja';
        $serviceName = $this->bookingServiceName($booking, strtolower($type));

        PanditNotification::firstOrCreate(
            [
                'pandit_id' => $booking->pandit_id,
                'title' => 'New issue reported',
                'message' => 'A user reported an issue for '.$type.' booking #'.$booking->id.' - '.$serviceName.'. Open Reports to respond. Report #'.$dispute->id.'.',
            ],
            ['is_read' => false]
        );
    }

    private function bookingServiceName(Model $booking, string $type): string
    {
        $meta = $booking->admin_note ? (json_decode($booking->admin_note, true) ?: []) : [];

        return $meta[$type.'_name']
            ?? $booking->service?->name
            ?? ucfirst($type).' Booking';
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
