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

class PaymentHoldTest extends TestCase
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
                    'id' => 'order_hold_'.$this->orders,
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'status' => 'created',
                ]);
            },
        ]);
    }

    public function test_confirm_pay_creates_pending_booking_with_ten_minute_hold_and_db_amount(): void
    {
        [$pandit, $hawan, $service, $date] = $this->fixtures();
        $user = $this->user('hold@example.test');

        $this->actingAs($user)
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date, 'special')])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date, 'special', 999999, 501))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'payment_status' => 'pending',
            ]);

        $session = HawanSession::firstOrFail();
        $attempt = PaymentAttempt::firstOrFail();
        $donation = Donation::firstOrFail();

        $this->assertSame('pending', $session->status);
        $this->assertSame('pending', $session->payment_status);
        $this->assertNull($session->live_session_link);
        $this->assertTrue($session->payment_hold_expires_at->between(now()->addMinutes(9), now()->addMinutes(11)));
        $this->assertSame(5102.0, (float) $attempt->amount);
        $this->assertSame(5102.0, (float) $donation->amount);
        $this->assertSame($attempt->id, $session->latest_payment_attempt_id);
        $this->assertDatabaseCount('video_meetings', 0);
        $this->assertDatabaseHas('payment_logs', ['event_type' => 'payment_hold_created', 'status' => 'pending']);
    }

    public function test_expired_special_hold_does_not_block_slot(): void
    {
        [$pandit, $hawan, $service, $date] = $this->fixtures();
        $this->heldHawan($pandit, $hawan, $date, 'special', now()->subMinute());

        $this->actingAs($this->user('expiry@example.test'))
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date, 'special')])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date, 'special'))
            ->assertOk();

        $this->assertDatabaseCount('hawan_sessions', 2);
    }

    public function test_active_special_hold_blocks_same_pandit_slot(): void
    {
        [$pandit, $hawan, $service, $date] = $this->fixtures();

        $this->actingAs($this->user('first@example.test'))
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date, 'special')])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date, 'special'))
            ->assertOk();

        $this->actingAs($this->user('second@example.test'))
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date, 'special')])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date, 'special'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slot');

        $this->assertDatabaseCount('hawan_sessions', 1);
    }

    public function test_samuhik_capacity_counts_active_holds(): void
    {
        [$pandit, $hawan, $service, $date] = $this->fixtures();

        foreach (range(1, 5) as $index) {
            $this->heldHawan($pandit, $hawan, $date, 'samuhik', now()->addMinutes(10));
        }

        $this->actingAs($this->user('sixth@example.test'))
            ->withSession(['hawan_booking' => $this->bookingSession($pandit, $hawan, $service, $date, 'samuhik')])
            ->postJson(route('hawan.store'), $this->payload($hawan, $date, 'samuhik'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slot');

        $this->assertDatabaseCount('hawan_sessions', 5);
    }

    private function fixtures(): array
    {
        $date = Carbon::tomorrow('Asia/Kolkata');
        $pandit = Pandit::create([
            'full_name' => 'Hold Pandit',
            'pandit_name' => 'Hold Pandit',
            'email' => 'hold-pandit@example.test',
            'status' => 'verified',
        ]);
        $hawan = Hawan::create([
            'name' => 'Hold Hawan',
            'slug' => 'hold-hawan',
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
        $service = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'service_name' => 'Hold Hawan',
            'hawan_id' => $hawan->id,
            'status' => 'approved',
        ]);

        PanditOnlineSetup::create(['pandit_id' => $pandit->id, 'online_hawan' => true, 'stable_internet' => true]);
        PanditAvailabilitySlot::create([
            'pandit_id' => $pandit->id,
            'day' => $date->format('l'),
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'is_available' => true,
        ]);

        return [$pandit, $hawan, $service, $date];
    }

    private function bookingSession(Pandit $pandit, Hawan $hawan, PanditService $service, Carbon $date, string $type): array
    {
        return [
            'service_type' => 'hawan',
            'service_id' => $hawan->id,
            'service_slug' => $hawan->slug,
            'pandit_service_id' => $service->id,
            'pandit_id' => $pandit->id,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => $type === 'special' ? 'Special Hawan' : 'Samuhik Hawan',
            'hawan_type' => $type,
        ];
    }

    private function payload(Hawan $hawan, Carbon $date, string $type, int $clientAmount = 1, int $dakshina = 0): array
    {
        return [
            'hawan_slug' => $hawan->slug,
            'hawan_type' => $type,
            'package_name' => $type === 'special' ? 'Special Hawan' : 'Samuhik Hawan',
            'package_amount' => $clientAmount,
            'full_name' => 'Hold User',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
            'donation_amount' => $dakshina,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ];
    }

    private function heldHawan(Pandit $pandit, Hawan $hawan, Carbon $date, string $type, $expiresAt): HawanSession
    {
        return HawanSession::create([
            'user_id' => $this->user('held-'.uniqid().'@example.test')->id,
            'service_type' => 'hawan',
            'ritual_id' => $hawan->id,
            'ritual_slug' => $hawan->slug,
            'hawan_type' => $type,
            'hawan_type_title' => $type === 'special' ? 'Special Hawan' : 'Samuhik Hawan',
            'hawan_type_price' => $type === 'special' ? 4601 : 2101,
            'pandit_id' => $pandit->id,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_hold_started_at' => now(),
            'payment_hold_expires_at' => $expiresAt,
        ]);
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Hold User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }
}
