<?php

namespace Tests\Feature;

use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\BookingUserConfirmation;
use App\Models\Dispute;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditService;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\SessionCompletionProof;
use App\Models\User;
use App\Models\VideoMeetingAttendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingCompletionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_booking_cannot_submit_completion_proof_before_meeting_ended(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'online');

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.complete', ['type' => 'hawan', 'id' => $session->id]), [
                'completion_image' => $this->fakeCompletionImage(),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('completion');

        $this->assertSame('confirmed', $session->fresh()->status);
        $this->assertDatabaseCount('session_completion_proofs', 0);
    }

    public function test_online_meeting_ended_and_pandit_proof_marks_booking_completed(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'online');
        $this->meetingEnded($session);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.complete', ['type' => 'hawan', 'id' => $session->id]), [
                'completion_image' => $this->fakeCompletionImage(),
                'completion_note' => 'Completed with sankalp.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $session->refresh();

        $this->assertSame('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertDatabaseHas('session_completion_proofs', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'pandit_id' => $pandit->id,
            'proof_type' => 'completion_image',
            'notes' => 'Completed with sankalp.',
        ]);
        $this->assertDatabaseHas('booking_user_confirmations', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'user_id' => $user->id,
            'status' => BookingUserConfirmation::STATUS_PENDING,
        ]);
    }

    public function test_user_confirmation_marks_payout_ready(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'online');
        $this->completeByPandit($session, $pandit, true);

        $this->actingAs($user)
            ->post(route('live.completion.confirm', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_user_confirmations', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'status' => BookingUserConfirmation::STATUS_CONFIRMED,
        ]);
        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
    }

    public function test_open_dispute_keeps_payout_on_hold(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'online');
        $this->completeByPandit($session, $pandit, true);

        $this->actingAs($user)
            ->post(route('live.issue-report.store', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'session_incomplete',
                'description' => 'Session was incomplete.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $payout = PanditPayout::firstOrFail();

        $this->assertSame(PanditPayout::STATUS_HOLD, $payout->status);
        $this->assertDatabaseHas('booking_user_confirmations', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'status' => BookingUserConfirmation::STATUS_DISPUTED,
        ]);
    }

    public function test_offline_proof_works_without_video_meeting(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('pooja', 'offline');

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.complete', ['type' => 'pooja', 'id' => $session->id]), [
                'completion_image' => $this->fakeCompletionImage('offline-proof.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('completed', $session->fresh()->status);
        $this->assertDatabaseCount('video_meetings', 0);
        $this->assertDatabaseCount('video_meeting_attendances', 0);
    }

    public function test_offline_confirmed_completion_marks_payout_ready_without_zoom_attendance(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'offline');
        $this->completeByPandit($session, $pandit, false);

        $this->actingAs($user)
            ->post(route('live.completion.confirm', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect();

        $this->assertDatabaseCount('video_meeting_attendances', 0);
        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
    }

    public function test_completed_booking_shows_review_system_for_user_and_pandit(): void
    {
        Storage::fake('public');
        [$user, $pandit, $session] = $this->booking('hawan', 'offline');
        $this->completeByPandit($session, $pandit, false);

        $this->actingAs($user)
            ->get(route('live.session', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertSee('Pandit has marked this Hawan as completed')
            ->assertSee('Confirm Completed')
            ->assertSee('Review Pandit');

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.bookings.show', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertSee('Review Yajman');
    }

    public function test_auto_confirmation_works_after_24_hours_but_skips_open_dispute(): void
    {
        [$user, $pandit, $session] = $this->booking('hawan', 'offline');
        $this->makeCompletedWithPendingConfirmation($session, now()->subMinute());

        [$disputeUser, $disputePandit, $disputedSession] = $this->booking('pooja', 'offline');
        $this->makeCompletedWithPendingConfirmation($disputedSession, now()->subMinute());
        $disputedSession->disputes()->create([
            'user_id' => $disputeUser->id,
            'pandit_id' => $disputePandit->id,
            'reason' => 'session_incomplete',
            'description' => 'Needs review.',
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this->artisan('bookings:auto-confirm-completions')->assertExitCode(0);

        $this->assertDatabaseHas('booking_user_confirmations', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'status' => BookingUserConfirmation::STATUS_AUTO_CONFIRMED,
        ]);
        $this->assertDatabaseHas('booking_user_confirmations', [
            'session_type' => PoojaSession::class,
            'session_id' => $disputedSession->id,
            'status' => BookingUserConfirmation::STATUS_PENDING,
        ]);
        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::where('session_id', $session->id)->where('session_type', HawanSession::class)->firstOrFail()->status);
    }

    private function booking(string $type, string $mode): array
    {
        $user = User::create([
            'name' => uniqid('Completion User '),
            'email' => uniqid('completion-user').'@example.test',
            'password' => Hash::make('password'),
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Completion Pandit',
            'pandit_name' => 'Completion Pandit',
            'email' => uniqid('completion-pandit').'@example.test',
            'status' => 'verified',
        ]);

        if ($type === 'pooja') {
            $ritual = Pooja::create([
                'name' => 'Completion Pooja',
                'slug' => uniqid('completion-pooja-'),
                'base_price' => 1501,
                'status' => 'active',
            ]);
            $sessionClass = PoojaSession::class;
            $service = PanditService::create([
                'pandit_id' => $pandit->id,
                'service_type' => 'pooja',
                'service_name' => $ritual->name,
                'pooja_id' => $ritual->id,
                'status' => 'approved',
            ]);
        } else {
            $ritual = Hawan::create([
                'name' => 'Completion Hawan',
                'slug' => uniqid('completion-hawan-'),
                'base_price' => 2101,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_price' => 4601,
                'status' => 'active',
            ]);
            $sessionClass = HawanSession::class;
            $service = PanditService::create([
                'pandit_id' => $pandit->id,
                'service_type' => 'hawan',
                'service_name' => $ritual->name,
                'hawan_id' => $ritual->id,
                'status' => 'approved',
            ]);
        }

        $sessionData = [
            'user_id' => $user->id,
            'service_type' => $type,
            'booking_mode' => $mode,
            'state' => $mode === 'offline' ? 'Punjab' : null,
            'city' => $mode === 'offline' ? 'Lalru' : null,
            'ritual_id' => $ritual->id,
            'ritual_slug' => $ritual->slug,
            'pandit_id' => $pandit->id,
            'pandit_service_id' => $service->id,
            'booking_date' => Carbon::tomorrow('Asia/Kolkata')->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode([$type.'_name' => $ritual->name, 'package_amount' => 4601, 'total_amount' => 4601]),
        ];

        if ($type === 'hawan') {
            $sessionData['hawan_type'] = 'special';
            $sessionData['hawan_type_title'] = 'Special Hawan';
            $sessionData['hawan_type_price'] = 4601;
        }

        $session = $sessionClass::create($sessionData);

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => $sessionClass,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_completion_'.$session->id.'_'.uniqid(),
            'gateway_payment_id' => 'pay_completion_'.$session->id.'_'.uniqid(),
            'amount' => 4601,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
            'metadata' => ['total_amount' => 4601],
        ]);

        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return [$user, $pandit, $session->fresh()];
    }

    private function meetingEnded(HawanSession|PoojaSession $session): void
    {
        VideoMeetingAttendance::create([
            'session_type' => $session::class,
            'session_id' => $session->id,
            'event_type' => VideoMeetingAttendance::EVENT_MEETING_ENDED,
            'provider' => 'zoom',
            'left_at' => now(),
        ]);
    }

    private function completeByPandit(HawanSession|PoojaSession $session, Pandit $pandit, bool $meetingEnded): void
    {
        if ($meetingEnded) {
            $this->meetingEnded($session);
        }

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.complete', ['type' => $session instanceof HawanSession ? 'hawan' : 'pooja', 'id' => $session->id]), [
                'completion_image' => $this->fakeCompletionImage(),
            ])
            ->assertRedirect();
    }

    private function fakeCompletionImage(string $name = 'proof.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }

    private function makeCompletedWithPendingConfirmation(HawanSession|PoojaSession $session, $expiresAt): void
    {
        $session->update([
            'status' => 'completed',
            'completed_at' => now()->subHours(24),
        ]);
        SessionCompletionProof::create([
            'session_type' => $session::class,
            'session_id' => $session->id,
            'pandit_id' => $session->pandit_id,
            'user_id' => $session->user_id,
            'proof_type' => 'completion_image',
            'file_path' => 'completion-proofs/test.jpg',
            'status' => SessionCompletionProof::STATUS_PENDING,
            'submitted_at' => now()->subHours(24),
        ]);
        BookingUserConfirmation::create([
            'session_type' => $session::class,
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'status' => BookingUserConfirmation::STATUS_PENDING,
            'expires_at' => $expiresAt,
        ]);
    }
}
