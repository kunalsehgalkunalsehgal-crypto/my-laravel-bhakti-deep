<?php

namespace Tests\Feature;

use App\Models\Admin\Audio;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalPoojaPaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    private int $orders = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('poojas/videos/digital-pooja.mp4', 'video');
        Storage::disk('public')->put('audio/digital-payment-mantra.mp3', 'audio');

        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => function ($request) {
                $this->orders++;

                return Http::response([
                    'id' => 'order_digital_'.$this->orders,
                    'amount' => $request['amount'],
                    'currency' => 'INR',
                    'status' => 'created',
                ]);
            },
        ]);
    }

    public function test_successful_payment_activates_digital_video_without_payout_or_zoom(): void
    {
        [$user, $session, $attempt] = $this->startDigitalBooking();

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk()
            ->assertJsonPath('redirect_url', route('pooja.digital.show', $session));

        $session->refresh();

        $this->assertSame('active', $session->status);
        $this->assertSame('paid', $session->payment_status);
        $this->assertSame(120, $session->digital_access_minutes);
        $this->assertTrue($session->expires_at->equalTo($session->start_at->copy()->addMinutes(120)));
        $this->assertSame(PaymentAttempt::STATUS_PAID, $attempt->fresh()->status);
        $this->assertDatabaseCount('pandit_payouts', 0);
        $this->assertDatabaseCount('video_meetings', 0);

        $this->get(route('pooja.digital.show', $session))
            ->assertOk()
            ->assertSee('Recorded Digital Pooja')
            ->assertSee(route('pooja.digital.media', ['session' => $session, 'media' => 'video']), false)
            ->assertSee('autoplay muted loop playsinline', false)
            ->assertSee('Digital Payment Mantra')
            ->assertSee(route('pooja.digital.media', ['session' => $session, 'media' => 'audio']), false)
            ->assertDontSee('storage/poojas/videos/digital-pooja.mp4', false)
            ->assertDontSee('storage/audio/digital-payment-mantra.mp3', false)
            ->assertSee('Access Remaining:')
            ->assertSee('Pooja Shuru Kare')
            ->assertSee('Digital Pooja Session Completed')
            ->assertSee('Restart')
            ->assertSee('Volume');

        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'video']))->assertOk();
        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'audio']))->assertOk();

        $this->get(route('user.profile'))
            ->assertOk()
            ->assertSee(route('pooja.digital.show', $session), false);
    }

    public function test_expired_digital_payment_can_retry_without_pandit_availability(): void
    {
        [$user, $session, $attempt] = $this->startDigitalBooking('retry-digital@example.test');
        $session->update(['payment_hold_expires_at' => now()->subMinute()]);
        $attempt->update(['hold_expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'pooja', 'id' => $session->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_digital_2');

        $this->assertTrue($session->fresh()->payment_hold_expires_at->isFuture());
        $this->assertDatabaseCount('payment_attempts', 2);
        $this->assertSame('751.00', PaymentAttempt::latest('id')->first()->amount);
        $this->assertDatabaseCount('pandits', 0);
    }

    public function test_digital_video_requires_owner_paid_digital_session(): void
    {
        [$owner, $session] = $this->startDigitalBooking('access-digital@example.test');

        $this->actingAs($owner)
            ->get(route('pooja.digital.show', $session))
            ->assertForbidden();

        $session->update([
            'status' => 'active',
            'payment_status' => 'paid',
            'start_at' => now(),
            'expires_at' => now()->addMinutes(120),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('pooja.digital.show', $session))
            ->assertForbidden();

        Auth::guard('web')->logout();
        $this->get(route('pooja.digital.show', $session))->assertRedirect(route('login'));

        $session->update(['pooja_type' => 'live']);
        $this->actingAs($owner)
            ->get(route('pooja.digital.show', $session))
            ->assertForbidden();
    }

    public function test_60_minute_expiry_is_snapshotted_and_admin_change_does_not_extend_it(): void
    {
        [$user, $session, $attempt, $pooja] = $this->startDigitalBooking('duration-digital@example.test', 60);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        $session->refresh();
        $originalExpiry = $session->expires_at->copy();

        $this->assertSame(60, $session->digital_access_minutes);
        $this->assertTrue($originalExpiry->equalTo($session->start_at->copy()->addMinutes(60)));

        $pooja->update(['digital_pooja_access_minutes' => 120]);

        $session->refresh();
        $this->assertSame(60, $session->digital_access_minutes);
        $this->assertTrue($session->expires_at->equalTo($originalExpiry));
    }

    public function test_expired_digital_page_video_and_audio_are_blocked_and_completed(): void
    {
        [$user, $session, $attempt] = $this->startDigitalBooking('expired-digital@example.test', 60);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        $this->get(route('pooja.digital.show', $session))->assertOk();
        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'video']))->assertOk();
        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'audio']))->assertOk();

        $session->update(['expires_at' => now()->subSecond()]);

        $this->get(route('pooja.digital.show', $session))->assertForbidden();
        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'video']))->assertForbidden();
        $this->get(route('pooja.digital.media', ['session' => $session, 'media' => 'audio']))->assertForbidden();

        $session->refresh();
        $this->assertSame('completed', $session->status);
        $this->assertNotNull($session->completed_at);
    }

    private function startDigitalBooking(string $email = 'digital-payment@example.test', int $accessMinutes = 120): array
    {
        $user = User::factory()->create(['email' => $email]);
        $mantra = Audio::create([
            'title' => 'Digital Payment Mantra',
            'slug' => 'digital-payment-mantra-'.uniqid(),
            'category' => 'mantra',
            'audio_file' => 'audio/digital-payment-mantra.mp3',
            'status' => 'active',
        ]);
        $pooja = Pooja::create([
            'name' => 'Digital Payment Pooja',
            'slug' => 'digital-payment-pooja',
            'base_price' => 100,
            'live_pooja_enabled' => false,
            'digital_pooja_enabled' => true,
            'digital_pooja_title' => 'Recorded Digital Pooja',
            'digital_pooja_price' => 751,
            'digital_pooja_video' => 'poojas/videos/digital-pooja.mp4',
            'digital_pooja_audio_id' => $mantra->id,
            'digital_pooja_access_minutes' => $accessMinutes,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson(route('pooja.store'), [
                'pooja_slug' => $pooja->slug,
                'pooja_type' => 'digital',
                'full_name' => 'Digital Devotee',
                'mobile' => '9999999999',
                'purpose' => 'Peace',
            ])
            ->assertOk();

        $session = PoojaSession::firstOrFail();

        return [$user, $session, $session->latestPaymentAttempt, $pooja];
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
}
