<?php

namespace Tests\Feature;

use App\Models\Admin\Donation;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditService;
use App\Models\PaymentAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RazorpayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private int $orders = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => function ($request) {
                $this->orders++;

                return Http::response([
                    'id' => 'order_test_'.$this->orders,
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'status' => 'created',
                ]);
            },
        ]);
    }

    public function test_payment_success_marks_booking_scheduled_and_paid(): void
    {
        [$user, $session, $attempt] = $this->startBooking();

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('scheduled', $session->fresh()->status);
        $this->assertSame('paid', $session->fresh()->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_PAID, $attempt->fresh()->status);
        $this->assertSame('paid', Donation::first()->payment_status);
        $this->assertDatabaseCount('video_meetings', 0);

        Http::assertSent(fn ($request) => $request['amount'] === 510200);
    }

    public function test_payment_failure_marks_only_attempt_failed_and_profile_shows_retry(): void
    {
        [$user, $session, $attempt] = $this->startBooking('fail@example.test');

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.failure'), [
                'payment_attempt_id' => $attempt->id,
                'razorpay_order_id' => $attempt->gateway_order_id,
                'error' => ['description' => 'Card declined'],
            ])
            ->assertOk();

        $this->assertSame(PaymentAttempt::STATUS_FAILED, $attempt->fresh()->status);
        $this->assertSame('pending', $session->fresh()->status);
        $this->assertSame('pending', $session->fresh()->payment_status);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Retry Payment');
    }

    public function test_cancelled_refunded_booking_does_not_show_or_allow_retry(): void
    {
        [$user, $session] = $this->startBooking('closed-retry@example.test');

        $session->update([
            'status' => 'cancelled_by_pandit',
            'payment_status' => 'refunded',
        ]);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertDontSee('Retry Payment');

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'hawan', 'id' => $session->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment');

        $this->assertSame(1, $this->orders);
        $this->assertDatabaseCount('payment_attempts', 1);
    }

    public function test_cancelled_refunded_booking_cannot_be_paid_by_old_attempt(): void
    {
        [$user, $session, $attempt] = $this->startBooking('closed-verify@example.test');

        $session->update([
            'status' => 'cancelled_by_pandit',
            'payment_status' => 'refunded',
        ]);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertUnprocessable()
            ->assertJson(['message' => 'This booking cannot be paid again.']);

        $session->refresh();

        $this->assertSame('cancelled_by_pandit', $session->status);
        $this->assertSame('refunded', $session->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_CANCELLED, $attempt->fresh()->status);
    }

    public function test_retry_uses_same_booking_with_new_razorpay_order(): void
    {
        [$user, $session, $attempt] = $this->startBooking('retry@example.test');

        $this->actingAs($user)->postJson(route('payments.razorpay.failure'), [
            'payment_attempt_id' => $attempt->id,
            'razorpay_order_id' => $attempt->gateway_order_id,
        ]);

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_test_2');

        $this->assertDatabaseCount('hawan_sessions', 1);
        $this->assertDatabaseCount('payment_attempts', 2);
        $this->assertSame($session->id, PaymentAttempt::latest('id')->first()->payable_id);
        $this->assertSame(PaymentAttempt::STATUS_FAILED, $attempt->fresh()->status);
    }

    public function test_invalid_signature_does_not_mark_booking_paid(): void
    {
        [$user, $session, $attempt] = $this->startBooking('bad-signature@example.test');

        $payload = $this->successPayload($attempt);
        $payload['razorpay_signature'] = 'bad-signature';

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $payload)
            ->assertUnprocessable();

        $this->assertSame(PaymentAttempt::STATUS_FAILED, $attempt->fresh()->status);
        $this->assertSame('pending', $session->fresh()->payment_status);
    }

    public function test_duplicate_success_is_ignored(): void
    {
        [$user, $session, $attempt] = $this->startBooking('duplicate@example.test');
        $payload = $this->successPayload($attempt);

        $this->actingAs($user)->postJson(route('payments.razorpay.verify'), $payload)->assertOk();
        $this->actingAs($user)->postJson(route('payments.razorpay.verify'), $payload)->assertOk();

        $this->assertSame('paid', $session->fresh()->payment_status);
        $this->assertDatabaseCount('payment_attempts', 1);
        $this->assertSame(1, \App\Models\Admin\PaymentLog::where('event_type', 'payment_verified')->count());
    }

    public function test_expired_hold_retry_refreshes_hold_when_slot_is_available(): void
    {
        [$user, $session] = $this->startBooking('expired-available@example.test');
        $session->update(['payment_hold_expires_at' => now()->subMinute()]);
        $session->latestPaymentAttempt->update(['hold_expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_test_2');

        $this->assertTrue($session->fresh()->payment_hold_expires_at->isFuture());
        $this->assertDatabaseCount('payment_attempts', 2);
    }

    public function test_expired_hold_retry_stops_when_slot_is_unavailable(): void
    {
        [$user, $session] = $this->startBooking('expired-blocked@example.test');
        $session->update(['payment_hold_expires_at' => now()->subMinute()]);
        $session->latestPaymentAttempt->update(['hold_expires_at' => now()->subMinute()]);
        $this->heldSpecial($session);

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'hawan', 'id' => $session->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slot');

        $this->assertDatabaseCount('payment_attempts', 1);
        $this->assertSame(1, $this->orders);
    }

    private function startBooking(string $email = 'success@example.test'): array
    {
        [$pandit, $hawan, $service, $date] = $this->fixtures();
        $user = $this->user($email);

        $this->actingAs($user)
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date)])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_test_'.$this->orders);

        return [$user, HawanSession::firstOrFail(), PaymentAttempt::firstOrFail()];
    }

    private function successPayload(PaymentAttempt $attempt): array
    {
        $paymentId = 'pay_'.$attempt->id;

        return [
            'payment_attempt_id' => $attempt->id,
            'razorpay_order_id' => $attempt->gateway_order_id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => hash_hmac('sha256', $attempt->gateway_order_id.'|'.$paymentId, 'rzp_test_secret'),
        ];
    }

    private function fixtures(): array
    {
        $date = Carbon::tomorrow('Asia/Kolkata');
        $pandit = Pandit::first() ?: Pandit::create([
            'full_name' => 'Razorpay Pandit',
            'pandit_name' => 'Razorpay Pandit',
            'email' => 'razorpay-pandit@example.test',
            'status' => 'verified',
        ]);
        $hawan = Hawan::first() ?: Hawan::create([
            'name' => 'Razorpay Hawan',
            'slug' => 'razorpay-hawan',
            'base_price' => 2101,
            'samuhik_hawan_enabled' => true,
            'samuhik_hawan_title' => 'Samuhik Hawan',
            'samuhik_hawan_price' => 2101,
            'special_hawan_enabled' => true,
            'special_hawan_title' => 'Special Hawan',
            'special_hawan_price' => 4601,
            'available_slots' => ['7:00 AM - 8:00 AM'],
            'status' => 'active',
        ]);
        $service = PanditService::first() ?: PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'service_name' => $hawan->name,
            'hawan_id' => $hawan->id,
            'status' => 'approved',
        ]);

        PanditOnlineSetup::firstOrCreate(['pandit_id' => $pandit->id], ['online_hawan' => true, 'stable_internet' => true]);
        PanditAvailabilitySlot::firstOrCreate([
            'pandit_id' => $pandit->id,
            'day' => $date->format('l'),
        ], [
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'is_available' => true,
        ]);

        return [$pandit, $hawan, $service, $date];
    }

    private function bookingSession(Pandit $pandit, Hawan $hawan, PanditService $service, Carbon $date): array
    {
        return [
            'service_type' => 'hawan',
            'service_id' => $hawan->id,
            'service_slug' => $hawan->slug,
            'pandit_service_id' => $service->id,
            'pandit_id' => $pandit->id,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Special Hawan',
            'hawan_type' => 'special',
        ];
    }

    private function payload(Hawan $hawan, Carbon $date): array
    {
        return [
            'hawan_slug' => $hawan->slug,
            'hawan_type' => 'special',
            'package_name' => 'Special Hawan',
            'package_amount' => 1,
            'full_name' => 'Razorpay User',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
            'donation_amount' => 501,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ];
    }

    private function heldSpecial(HawanSession $session): void
    {
        HawanSession::create([
            'user_id' => $this->user('holder@example.test')->id,
            'service_type' => 'hawan',
            'ritual_id' => $session->ritual_id,
            'ritual_slug' => $session->ritual_slug,
            'hawan_type' => 'special',
            'pandit_id' => $session->pandit_id,
            'booking_date' => $session->booking_date,
            'slot' => $session->slot,
            'slot_start_time' => $session->slot_start_time,
            'slot_end_time' => $session->slot_end_time,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_hold_started_at' => now(),
            'payment_hold_expires_at' => now()->addMinutes(10),
        ]);
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Razorpay User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }
}
