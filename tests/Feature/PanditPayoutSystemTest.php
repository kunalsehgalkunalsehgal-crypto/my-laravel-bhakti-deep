<?php

namespace Tests\Feature;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\BookingUserConfirmation;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditBankDetail;
use App\Models\Pandit\PanditService;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\SessionCompletionProof;
use App\Models\User;
use App\Models\VideoMeetingAttendance;
use App\Services\PanditPayoutLedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_dispute_cannot_be_opened_after_customer_confirmation(): void
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
            ->assertSessionHasErrors('dispute');

        $payout = PanditPayout::firstOrFail()->fresh();

        $this->assertSame(PanditPayout::STATUS_READY, $payout->status);
        $this->assertNotNull($payout->eligible_at);
        $this->assertDatabaseCount('disputes', 0);
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

    public function test_admin_can_manually_settle_ready_payouts_and_cannot_reuse_the_utr(): void
    {
        config(['services.payouts.mode' => 'manual', 'services.payouts.route_enabled' => false]);
        [$admin, $user, $session] = $this->paidBooking(status: 'completed');
        $this->makeEligible($session);
        app(PanditPayoutLedgerService::class)->syncForBooking($session);
        $first = PanditPayout::firstOrFail();
        $second = PanditPayout::create([
            'pandit_id' => $session->pandit_id,
            'payout_type' => PanditPayout::TYPE_BOOKING,
            'service_amount' => 250,
            'booking_amount' => 250,
            'pandit_amount' => 250,
            'payout_amount' => 250,
            'currency' => 'INR',
            'status' => PanditPayout::STATUS_READY,
            'eligible_at' => now(),
        ]);
        $bank = PanditBankDetail::create([
            'pandit_id' => $session->pandit_id,
            'bank_name' => 'HDFC Bank',
            'account_number' => '1234567890',
            'ifsc_code' => 'HDFC0001234',
            'upi_id' => 'pandit@upi',
        ]);
        $bank->upi_qr_path = 'pandits/upi-qr/test.png';
        $bank->save();

        $this->actingAs($admin, 'admin')->get(route('admin.payouts.index'))
            ->assertOk()
            ->assertSee('Ready Manual Settlements')
            ->assertSee('HDFC Bank')
            ->assertSee('1234567890')
            ->assertSee('HDFC0001234')
            ->assertSee('pandit@upi')
            ->assertSee('storage/pandits/upi-qr/test.png', false);

        $this->actingAs($admin, 'admin')->post(route('admin.payouts.manual-settle'), [
            'payout_ids' => [$first->id, $second->id],
            'payment_method' => 'upi',
            'utr' => 'utr-12345',
            'amount' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        foreach ([$first->fresh(), $second->fresh()] as $payout) {
            $this->assertSame(PanditPayout::STATUS_PAID, $payout->status);
            $this->assertSame('UTR-12345', $payout->payout_reference);
            $this->assertSame((float) $payout->pandit_amount, (float) $payout->payout_amount);
            $this->assertSame(5352.0, (float) data_get($payout->metadata, 'manual_settlement.amount'));
            $this->assertSame('upi', data_get($payout->metadata, 'manual_settlement.payment_method'));
            $this->assertNotNull($payout->paid_at);
        }

        $this->actingAs($admin, 'admin')->get(route('admin.payouts.index', ['status' => PanditPayout::STATUS_PAID]))
            ->assertOk()
            ->assertSee('Payout History')
            ->assertSee('UTR-12345')
            ->assertSee('UPI');

        $this->actingAs($admin, 'admin')->post(route('admin.payouts.manual-settle'), [
            'payout_ids' => [$first->id, $second->id],
            'payment_method' => 'upi',
            'utr' => 'UTR-SECOND',
        ])->assertSessionHasErrors('manual_payout');

        $third = PanditPayout::create([
            'pandit_id' => $session->pandit_id,
            'payout_type' => PanditPayout::TYPE_BOOKING,
            'pandit_amount' => 100,
            'payout_amount' => 100,
            'currency' => 'INR',
            'status' => PanditPayout::STATUS_READY,
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.payouts.manual-settle'), [
            'payout_ids' => [$third->id],
            'payment_method' => 'upi',
            'utr' => 'utr-12345',
        ])->assertSessionHasErrors('utr');
        $this->assertSame(PanditPayout::STATUS_READY, $third->fresh()->status);

        config(['services.payouts.mode' => 'route', 'services.payouts.route_enabled' => true]);
        $this->actingAs($admin, 'admin')->post(route('admin.payouts.manual-settle'), [
            'payout_ids' => [$third->id],
            'payment_method' => 'upi',
            'utr' => 'UTR-NEW',
        ])->assertSessionHasErrors('manual_payout');
        $this->assertSame(PanditPayout::STATUS_READY, $third->fresh()->status);
    }

    public function test_pandit_can_upload_upi_qr_from_bank_details(): void
    {
        Storage::fake('public');
        $pandit = Pandit::create([
            'full_name' => 'QR Pandit',
            'pandit_name' => 'QR Pandit',
            'email' => 'qr-pandit@example.test',
            'status' => 'verified',
        ]);

        $this->actingAs($pandit, 'pandit')->post(route('pandit.bank-details.update'), [
            'account_holder_name' => 'Pandit One',
            'bank_name' => 'HDFC Bank',
            'account_number' => '1234567890',
            'ifsc_code' => 'HDFC0001234',
            'upi_id' => 'pandit@upi',
            'upi_qr' => UploadedFile::fake()->createWithContent(
                'upi-qr.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ),
        ])->assertRedirect()->assertSessionHas('success');

        $path = PanditBankDetail::where('pandit_id', $pandit->id)->firstOrFail()->upi_qr_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $pandit->unsetRelation('bankDetail');
        $this->actingAs($pandit, 'pandit')->get(route('pandit.bank-details'))
            ->assertOk()
            ->assertSee('storage/'.$path, false);
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
            'file_path' => 'completion-proofs/test.jpg',
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
