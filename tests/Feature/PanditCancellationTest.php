<?php

namespace Tests\Feature;

use App\Mail\BookingRefundMail;
use App\Models\Admin\Donation;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditService;
use App\Models\PanditPayout;
use App\Models\PaymentAttempt;
use App\Models\PaymentRefund;
use App\Models\User;
use App\Services\PanditBookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PanditCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_cancel_1/refund' => Http::response([
                'id' => 'rfnd_cancel_1',
                'amount' => 510200,
                'currency' => 'INR',
                'payment_id' => 'pay_cancel_1',
                'status' => 'processed',
            ]),
        ]);

        Mail::fake();
    }

    public function test_pandit_can_cancel_own_paid_booking(): void
    {
        [$pandit, $session] = $this->paidBooking();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'I am unavailable for this slot.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Refund of ₹5,102 initiated.')
            ->assertSessionHas('info', 'Refund Processed ₹5,102');

        $session->refresh();
        $this->assertSame('cancelled_by_pandit', $session->status);
        $this->assertSame('refunded', $session->payment_status);
        $this->assertSame('I am unavailable for this slot.', $session->pandit_cancel_reason);
        $this->assertNotNull($session->pandit_cancelled_at);
        $this->assertSame($pandit->id, $session->cancelled_by_pandit_id);
        $this->assertDatabaseHas('notifications', ['user_id' => $session->user_id, 'message' => 'Refund Processed ₹5,102']);
        Mail::assertSent(BookingRefundMail::class);
    }

    public function test_cancel_initiates_full_razorpay_refund_and_marks_payout_not_eligible(): void
    {
        [$pandit, $session, $attempt] = $this->paidBooking();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'Emergency cancellation.',
            ])
            ->assertRedirect();

        $refund = PaymentRefund::firstOrFail();
        $this->assertSame($attempt->id, $refund->payment_attempt_id);
        $this->assertSame('rfnd_cancel_1', $refund->gateway_refund_id);
        $this->assertSame(PaymentRefund::STATUS_REFUNDED, $refund->status);
        $this->assertSame(5102.0, (float) $refund->amount);
        $this->assertSame('refunded', Donation::first()->payment_status);
        $this->assertSame(5102.0, (float) Donation::first()->refunded_amount);
        $this->assertDatabaseHas('pandit_payouts', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'status' => PanditPayout::STATUS_CANCELLED,
            'payout_amount' => 0,
        ]);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v1/payments/pay_cancel_1/refund')
            && $request['amount'] === 510200
            && $request['speed'] === 'optimum');
    }

    public function test_cancelled_pandit_booking_keeps_slot_blocked_until_original_slot_ends(): void
    {
        [$pandit, $session] = $this->paidBooking();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'Cannot attend.',
            ]);

        $blocked = app(PanditBookingService::class)->hasBlockingHawanBooking(
            $pandit->id,
            $session->ritual_id,
            'special',
            $session->booking_date->toDateString(),
            $session->slot_start_time,
            $session->slot_end_time
        );

        $this->assertTrue($blocked);
    }

    public function test_duplicate_refund_is_blocked(): void
    {
        [$pandit, $session] = $this->paidBooking();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'First cancel.',
            ])
            ->assertRedirect();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.cancel', ['type' => 'hawan', 'id' => $session->id]), [
                'reason' => 'Second cancel.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('payment_refunds', 1);
        Http::assertSentCount(1);
    }

    private function paidBooking(): array
    {
        $date = Carbon::tomorrow('Asia/Kolkata');
        $user = User::create([
            'name' => 'Refund User',
            'email' => 'refund-user@example.test',
            'password' => Hash::make('password'),
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Refund Pandit',
            'pandit_name' => 'Refund Pandit',
            'email' => 'refund-pandit@example.test',
            'status' => 'verified',
        ]);
        $hawan = Hawan::create([
            'name' => 'Refund Hawan',
            'slug' => 'refund-hawan',
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
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => $hawan->name, 'total_amount' => 5102]),
        ]);
        $donation = Donation::create([
            'user_id' => $user->id,
            'payment_purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'amount' => 5102,
            'currency' => 'INR',
            'payment_status' => 'paid',
            'razorpay_order_id' => 'order_cancel_1',
            'razorpay_payment_id' => 'pay_cancel_1',
            'paid_at' => now(),
        ]);
        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'donation_id' => $donation->id,
            'payable_type' => HawanSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_BOOKING,
            'gateway' => 'razorpay_test',
            'gateway_order_id' => 'order_cancel_1',
            'gateway_payment_id' => 'pay_cancel_1',
            'amount' => 5102,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return [$pandit, $session->fresh(), $attempt];
    }
}
