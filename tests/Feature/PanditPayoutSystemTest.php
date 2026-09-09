<?php

namespace Tests\Feature;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\BookingUserConfirmation;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditService;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\SessionCompletionProof;
use App\Models\User;
use App\Models\VideoMeetingAttendance;
use App\Services\PanditPayoutLedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanditPayoutSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_completing_paid_service_with_no_dispute_marks_payout_ready(): void
    {
        [$admin, $user, $session, $attempt] = $this->paidBooking();
        $this->makeEligible($session);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.bookings.update', ['type' => 'hawan', 'id' => $session->id]), [
                'status' => 'completed',
                'payment_status' => 'paid',
                'admin_note' => $session->admin_note,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $session->refresh();
        $payout = PanditPayout::firstOrFail();

        $this->assertSame('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertSame($attempt->id, $payout->payment_attempt_id);
        $this->assertSame(PanditPayout::STATUS_READY, $payout->status);
        $this->assertSame($session->pandit_id, $payout->pandit_id);
        $this->assertSame(5102.0, (float) $payout->booking_amount);
        $this->assertSame(5102.0, (float) $payout->pandit_amount);
        $this->assertSame(0.0, (float) $payout->platform_amount);
        $this->assertNotNull($payout->eligible_at);
        $this->assertNull($payout->paid_at);
        $this->assertNull($payout->provider_payout_id);
    }

    public function test_completed_booking_with_meeting_ended_alone_keeps_payout_on_hold(): void
    {
        [$admin, $user, $session] = $this->paidBooking();
        VideoMeetingAttendance::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'event_type' => VideoMeetingAttendance::EVENT_MEETING_ENDED,
            'provider' => 'zoom',
            'left_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.bookings.update', ['type' => 'hawan', 'id' => $session->id]), [
                'status' => 'completed',
                'payment_status' => 'paid',
                'admin_note' => $session->admin_note,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $payout = PanditPayout::firstOrFail();

        $this->assertSame(PanditPayout::STATUS_HOLD, $payout->status);
        $this->assertNull($payout->eligible_at);
    }

    public function test_open_dispute_keeps_completed_booking_payout_on_hold(): void
    {
        [$admin, $user, $session] = $this->paidBooking();
        $this->makeEligible($session);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.bookings.update', ['type' => 'hawan', 'id' => $session->id]), [
                'status' => 'completed',
                'payment_status' => 'paid',
                'admin_note' => $session->admin_note,
            ]);

        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);

        $this->actingAs($user)
            ->post(route('live.issue-report.store', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'session_incomplete',
                'description' => 'The session ended too early.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Issue Reported - Status: Open');

        $payout = PanditPayout::firstOrFail()->fresh();

        $this->assertSame(PanditPayout::STATUS_HOLD, $payout->status);
        $this->assertNull($payout->eligible_at);
        $this->assertDatabaseCount('pandit_payouts', 1);
    }

    public function test_payout_sync_is_duplicate_protected(): void
    {
        [$admin, $user, $session] = $this->paidBooking(status: 'completed');
        $this->makeEligible($session);

        app(PanditPayoutLedgerService::class)->syncForBooking($session);
        app(PanditPayoutLedgerService::class)->syncForBooking($session->fresh());

        $this->assertDatabaseCount('pandit_payouts', 1);
        $this->assertSame(PanditPayout::STATUS_READY, PanditPayout::firstOrFail()->status);
    }

    public function test_admin_can_view_ready_payouts_when_provider_is_pending(): void
    {
        [$admin, $user, $session] = $this->paidBooking(status: 'completed');
        $this->makeEligible($session);
        app(PanditPayoutLedgerService::class)->syncForBooking($session);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.payouts.index', ['status' => PanditPayout::STATUS_READY]))
            ->assertOk()
            ->assertSee('Provider Pending')
            ->assertSee('Ready')
            ->assertSee('Bank payout pending')
            ->assertSee('HAWAN-'.$session->id)
            ->assertSee('Rs 5,102');
    }

    private function paidBooking(string $status = 'scheduled'): array
    {
        $role = AdminRole::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'status' => 'active']);
        $admin = Admin::create(['name' => 'Admin', 'email' => uniqid('admin').'@example.test', 'password' => 'password', 'role_id' => $role->id, 'status' => 'active']);
        $user = User::create(['name' => 'Payout User', 'email' => uniqid('payout-user').'@example.test', 'password' => Hash::make('password')]);
        $pandit = Pandit::create([
            'full_name' => 'Payout Pandit',
            'pandit_name' => 'Payout Pandit',
            'email' => uniqid('payout-pandit').'@example.test',
            'status' => 'verified',
        ]);
        $hawan = Hawan::create([
            'name' => 'Payout Hawan',
            'slug' => uniqid('payout-hawan-'),
            'base_price' => 2101,
            'special_hawan_enabled' => true,
            'special_hawan_title' => 'Special Hawan',
            'special_hawan_price' => 4601,
            'status' => 'active',
        ]);
        $service = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'service_name' => $hawan->name,
            'hawan_id' => $hawan->id,
            'status' => 'approved',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'service_type' => 'hawan',
            'ritual_id' => $hawan->id,
            'ritual_slug' => $hawan->slug,
            'hawan_type' => 'special',
            'hawan_type_title' => 'Special Hawan',
            'hawan_type_price' => 4601,
            'pandit_id' => $pandit->id,
            'pandit_service_id' => $service->id,
            'booking_date' => Carbon::tomorrow('Asia/Kolkata')->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => $status,
            'payment_status' => 'paid',
            'completed_at' => $status === 'completed' ? now() : null,
            'admin_note' => json_encode(['hawan_name' => $hawan->name, 'package_amount' => 4601, 'dakshina' => 501, 'total_amount' => 5102]),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_payout_'.$session->id,
            'gateway_payment_id' => 'pay_payout_'.$session->id,
            'amount' => 5102,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
            'metadata' => ['dakshina' => 501, 'total_amount' => 5102],
        ]);

        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return [$admin, $user, $session->fresh(), $attempt];
    }

    private function makeEligible(HawanSession $session): void
    {
        VideoMeetingAttendance::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'event_type' => VideoMeetingAttendance::EVENT_MEETING_ENDED,
            'provider' => 'zoom',
            'left_at' => now(),
        ]);
        SessionCompletionProof::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'pandit_id' => $session->pandit_id,
            'user_id' => $session->user_id,
            'status' => SessionCompletionProof::STATUS_PENDING,
            'submitted_at' => now(),
        ]);
        BookingUserConfirmation::create([
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'status' => BookingUserConfirmation::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }
}
