<?php

namespace Tests\Feature;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\AdminRole;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\SankalpForm;
use App\Models\Dispute;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditNotification;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\PaymentRefund;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Models\VideoMeetingAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDisputePanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);
    }

    public function test_admin_can_list_filter_and_view_dispute_details(): void
    {
        Storage::fake('local');
        [$admin, $dispute, $booking] = $this->fixture();

        $this->get(route('admin.disputes.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.disputes.index', ['status' => Dispute::STATUS_OPEN]))
            ->assertOk()
            ->assertSee('#'.$dispute->id)
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Aarav Sharma')
            ->assertSee('Pandit Test')
            ->assertSee('Mahamrityunjaya Hawan')
            ->assertSee('Technical issue')
            ->assertSee('Open');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.disputes.show', $dispute))
            ->assertOk()
            ->assertSee('Booking + Payment')
            ->assertSee('Total Paid')
            ->assertSee('Rs 4,500')
            ->assertSee('razorpay')
            ->assertSee('User Complaint')
            ->assertSee('user-proof.pdf')
            ->assertSee('Pandit Response')
            ->assertSee('pandit-proof.jpg')
            ->assertSee('Zoom Attendance')
            ->assertSee('Participant joined')
            ->assertSee('Refund User')
            ->assertSee('Resolve in Pandit Favour');
    }

    public function test_admin_can_move_open_dispute_to_under_review_and_add_note_only(): void
    {
        [$admin, $dispute, $booking] = $this->fixture();

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.disputes.update', $dispute), [
                'status' => Dispute::STATUS_UNDER_REVIEW,
                'admin_review_note' => 'Checking Zoom attendance before next action.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $dispute->refresh();
        $this->assertSame(Dispute::STATUS_UNDER_REVIEW, $dispute->status);
        $this->assertSame('Checking Zoom attendance before next action.', $dispute->admin_review_note);
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_id' => $admin->id,
            'module' => 'Disputes',
            'action' => 'review_update',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_under_review_'.$dispute->id,
            'message' => 'Your report #'.$dispute->id.' is under review.',
            'delivery_status' => 'sent',
        ]);
        $this->assertDatabaseHas('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Report under admin review',
            'message' => 'Report #'.$dispute->id.' is under admin review. Your payout remains on hold.',
            'is_read' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.disputes.update', $dispute), [
                'status' => Dispute::STATUS_UNDER_REVIEW,
                'admin_review_note' => 'Still checking.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, NotificationLog::where('message_type', 'report_under_review_'.$dispute->id)->count());
        $this->assertSame(1, PanditNotification::where('pandit_id', $booking->pandit_id)
            ->where('title', 'Report under admin review')
            ->where('message', 'Report #'.$dispute->id.' is under admin review. Your payout remains on hold.')
            ->count());

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.disputes.update', $dispute), ['status' => Dispute::STATUS_RESOLVED])
            ->assertSessionHasErrors('status');

        $this->assertSame(Dispute::STATUS_UNDER_REVIEW, $dispute->fresh()->status);
    }

    public function test_admin_detail_shows_not_available_when_attendance_missing(): void
    {
        [$admin, $dispute] = $this->fixture(withAttendance: false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.disputes.show', $dispute))
            ->assertOk()
            ->assertSee('Zoom Attendance')
            ->assertSee('Not Available');
    }

    public function test_admin_can_view_dispute_evidence_securely(): void
    {
        Storage::fake('local');
        [$admin, $dispute] = $this->fixture();
        $evidence = $dispute->evidences()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.disputes.evidence', [$dispute, $evidence]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_refund_user_through_razorpay_and_resolve_dispute(): void
    {
        [$admin, $dispute, $booking] = $this->fixture();

        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_test/refund' => Http::response([
                'id' => 'rfnd_dispute_1',
                'amount' => 450000,
                'currency' => 'INR',
                'payment_id' => 'pay_test',
                'status' => 'processed',
            ]),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect()
            ->assertSessionHas('success', 'Refund processed by Razorpay and dispute resolved.');

        $refund = PaymentRefund::firstOrFail();
        $dispute->refresh();

        $this->assertSame(Dispute::STATUS_RESOLVED, $dispute->status);
        $this->assertSame(Dispute::RESOLUTION_REFUND_USER, $dispute->resolution);
        $this->assertSame($admin->id, $dispute->resolved_by_admin_id);
        $this->assertNotNull($dispute->resolved_at);
        $this->assertSame($refund->id, $dispute->payment_refund_id);
        $this->assertSame('rfnd_dispute_1', $refund->gateway_refund_id);
        $this->assertSame('processed', $refund->gateway_status);
        $this->assertSame(PaymentRefund::STATUS_REFUNDED, $refund->status);
        $this->assertSame('refunded', $booking->fresh()->payment_status);
        $this->assertDatabaseHas('pandit_payouts', [
            'payment_attempt_id' => $booking->latest_payment_attempt_id,
            'status' => PanditPayout::STATUS_CANCELLED,
            'pandit_amount' => 0,
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_id' => $admin->id,
            'module' => 'Disputes',
            'action' => 'dispute_refund_user',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_user_favour_'.$dispute->id,
            'message' => 'Your report #'.$dispute->id.' was resolved in your favour. Refund has been initiated.',
            'delivery_status' => 'sent',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_refund_processed_'.$refund->id,
            'message' => 'Refund Processed ₹4,500',
            'delivery_status' => 'sent',
        ]);
        $this->assertDatabaseHas('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Report resolved in user favour',
            'message' => 'Report #'.$dispute->id.' was resolved in the user\'s favour. Refund ₹4,500 has been initiated to the user. Your payout for Booking #'.$booking->id.' is cancelled.',
            'is_read' => false,
        ]);
        $this->assertDatabaseHas('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Refund processed',
            'message' => 'Refund ₹4,500 was processed to the user. No payout is due for Booking #'.$booking->id.'.',
            'is_read' => false,
        ]);

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.notifications'))
            ->assertOk()
            ->assertSee('Report #'.$dispute->id.' was resolved in the user&#039;s favour. Refund ₹4,500 has been initiated to the user. Your payout for Booking #'.$booking->id.' is cancelled.', false)
            ->assertSee('Refund ₹4,500 was processed to the user. No payout is due for Booking #'.$booking->id.'.');

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.reports.index'))
            ->assertOk()
            ->assertSee('#'.$dispute->id)
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Resolved in user&#039;s favour', false)
            ->assertSee('Refund: Refunded');

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.reports.show', $dispute))
            ->assertOk()
            ->assertSee('#'.$dispute->id)
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Resolved in user&#039;s favour', false)
            ->assertSee('Refund Status')
            ->assertSee('Refunded');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v1/payments/pay_test/refund')
            && $request['amount'] === 450000
            && $request['speed'] === 'optimum');
    }

    public function test_pending_razorpay_refund_resolves_dispute_but_keeps_booking_paid(): void
    {
        [$admin, $dispute, $booking] = $this->fixture();

        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_test/refund' => Http::response([
                'id' => 'rfnd_dispute_pending',
                'amount' => 450000,
                'currency' => 'INR',
                'payment_id' => 'pay_test',
                'status' => 'pending',
            ]),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect()
            ->assertSessionHas('success', 'Refund initiated with Razorpay and dispute resolved.');

        $refund = PaymentRefund::firstOrFail();

        $this->assertSame(Dispute::STATUS_RESOLVED, $dispute->fresh()->status);
        $this->assertSame('pending', $refund->gateway_status);
        $this->assertSame(PaymentRefund::STATUS_PROCESSING, $refund->status);
        $this->assertSame('paid', $booking->fresh()->payment_status);
        $this->assertDatabaseHas('pandit_payouts', [
            'payment_attempt_id' => $booking->latest_payment_attempt_id,
            'status' => PanditPayout::STATUS_CANCELLED,
            'pandit_amount' => 0,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_user_favour_'.$dispute->id,
            'message' => 'Your report #'.$dispute->id.' was resolved in your favour. Refund has been initiated.',
            'delivery_status' => 'sent',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_refund_processed_'.$refund->id,
        ]);
        $this->assertDatabaseHas('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Report resolved in user favour',
            'message' => 'Report #'.$dispute->id.' was resolved in the user\'s favour. Refund ₹4,500 has been initiated to the user. Your payout for Booking #'.$booking->id.' is cancelled.',
            'is_read' => false,
        ]);
        $this->assertDatabaseMissing('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Refund processed',
            'message' => 'Refund ₹4,500 was processed to the user. No payout is due for Booking #'.$booking->id.'.',
        ]);

        $refund->update(['status' => PaymentRefund::STATUS_REFUNDED, 'processed_at' => now()]);

        $this->assertSame(1, NotificationLog::where('message_type', 'report_refund_processed_'.$refund->id)->count());
        $this->assertSame(1, PanditNotification::where('pandit_id', $booking->pandit_id)
            ->where('title', 'Refund processed')
            ->where('message', 'Refund ₹4,500 was processed to the user. No payout is due for Booking #'.$booking->id.'.')
            ->count());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_refund_processed_'.$refund->id,
            'message' => 'Refund Processed ₹4,500',
            'delivery_status' => 'sent',
        ]);
    }

    public function test_admin_refund_blocks_duplicate_refund(): void
    {
        [$admin, $dispute] = $this->fixture();

        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_test/refund' => Http::response([
                'id' => 'rfnd_dispute_duplicate',
                'amount' => 450000,
                'currency' => 'INR',
                'payment_id' => 'pay_test',
                'status' => 'pending',
            ]),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect()
            ->assertSessionHasErrors('resolution');

        $this->assertDatabaseCount('payment_refunds', 1);
        Http::assertSentCount(1);
    }

    public function test_admin_refund_requires_paid_booking(): void
    {
        [$admin, $dispute, $booking] = $this->fixture();
        $booking->update(['payment_status' => 'pending']);
        Http::fake();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.refund-user', $dispute))
            ->assertRedirect()
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payment_refunds', 0);
        Http::assertNothingSent();
    }

    public function test_admin_can_resolve_dispute_in_pandit_favour_without_refund_or_bank_payout(): void
    {
        [$admin, $dispute, $booking] = $this->fixture();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.disputes.resolve-pandit-favour', $dispute))
            ->assertRedirect()
            ->assertSessionHas('success', 'Dispute resolved in pandit favour.');

        $dispute->refresh();

        $this->assertSame(Dispute::STATUS_RESOLVED, $dispute->status);
        $this->assertSame(Dispute::RESOLUTION_PANDIT_FAVOUR, $dispute->resolution);
        $this->assertSame($admin->id, $dispute->resolved_by_admin_id);
        $this->assertNotNull($dispute->resolved_at);
        $this->assertNull($dispute->payment_refund_id);
        $this->assertDatabaseCount('payment_refunds', 0);
        $this->assertDatabaseHas('pandit_payouts', [
            'payment_attempt_id' => $booking->latest_payment_attempt_id,
            'status' => PanditPayout::STATUS_READY,
            'booking_amount' => 4500,
            'pandit_amount' => 4500,
            'platform_amount' => 0,
            'provider_payout_id' => null,
            'paid_at' => null,
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_id' => $admin->id,
            'module' => 'Disputes',
            'action' => 'dispute_pandit_favour',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dispute->user_id,
            'channel' => 'my_bookings',
            'message_type' => 'report_pandit_favour_'.$dispute->id,
            'message' => 'Your report #'.$dispute->id.' has been reviewed and closed. No refund was issued.',
            'delivery_status' => 'sent',
        ]);
        $this->assertDatabaseHas('pandit_notifications', [
            'pandit_id' => $booking->pandit_id,
            'title' => 'Report resolved in pandit favour',
            'message' => 'Report #'.$dispute->id.' was resolved in your favour. No refund was issued. Your payout is now READY.',
            'is_read' => false,
        ]);

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.notifications'))
            ->assertOk()
            ->assertSee('Report #'.$dispute->id.' was resolved in your favour. No refund was issued. Your payout is now READY.');

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.reports.index'))
            ->assertOk()
            ->assertSee('#'.$dispute->id)
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Resolved in your favour')
            ->assertSee('Payout: Ready');

        $this->actingAs(Pandit::findOrFail($booking->pandit_id), 'pandit')
            ->get(route('pandit.reports.show', $dispute))
            ->assertOk()
            ->assertSee('#'.$dispute->id)
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Resolved in your favour')
            ->assertSee('Payout Status')
            ->assertSee('Ready');
    }

    private function fixture(bool $withAttendance = true): array
    {
        $role = AdminRole::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'status' => 'active']);
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role_id' => $role->id, 'status' => 'active']);
        $user = User::factory()->create(['name' => 'Aarav Sharma', 'email' => 'aarav-admin-dispute@example.test']);
        $pandit = Pandit::create(['full_name' => 'Pandit Test', 'pandit_name' => 'Pandit Test', 'email' => 'pandit-admin-dispute@example.test', 'status' => 'verified']);
        $sankalp = SankalpForm::create(['user_id' => $user->id, 'full_name' => 'Aarav Sharma', 'purpose' => 'Peace']);
        $booking = HawanSession::create([
            'user_id' => $user->id,
            'sankalp_form_id' => $sankalp->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Mahamrityunjaya Hawan', 'total_amount' => 4500]),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $booking->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_test',
            'gateway_payment_id' => 'pay_test',
            'amount' => 4500,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $booking->update(['latest_payment_attempt_id' => $attempt->id]);
        $meeting = VideoMeeting::create([
            'provider' => 'zoom',
            'external_meeting_id' => '123456789',
            'status' => 'scheduled',
            'pandit_id' => $pandit->id,
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
        ]);
        if ($withAttendance) {
            VideoMeetingAttendance::create([
                'video_meeting_id' => $meeting->id,
                'session_type' => HawanSession::class,
                'session_id' => $booking->id,
                'participant_type' => 'user',
                'participant_id' => $user->id,
                'zoom_participant_id' => 'zoom-user',
                'provider' => 'zoom',
                'event_type' => VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED,
                'joined_at' => now(),
                'provider_event_id' => 'admin-dispute-attendance',
            ]);
        }
        $dispute = $booking->disputes()->create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'reason' => 'technical_issue',
            'description' => 'Video was not stable.',
            'pandit_response' => 'I joined from Zoom link.',
            'pandit_responded_at' => now(),
            'status' => Dispute::STATUS_OPEN,
            'opened_at' => now(),
        ]);
        Storage::disk('local')->put('dispute-evidences/'.$dispute->id.'/user-proof.pdf', 'proof');
        Storage::disk('local')->put('dispute-evidences/'.$dispute->id.'/pandit/pandit-proof.jpg', 'proof');
        $dispute->evidences()->create(['uploaded_by_type' => User::class, 'uploaded_by_id' => $user->id, 'file_path' => 'dispute-evidences/'.$dispute->id.'/user-proof.pdf', 'original_name' => 'user-proof.pdf', 'mime_type' => 'application/pdf', 'file_size' => 5]);
        $dispute->evidences()->create(['uploaded_by_type' => Pandit::class, 'uploaded_by_id' => $pandit->id, 'file_path' => 'dispute-evidences/'.$dispute->id.'/pandit/pandit-proof.jpg', 'original_name' => 'pandit-proof.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 5]);

        return [$admin, $dispute, $booking];
    }
}
