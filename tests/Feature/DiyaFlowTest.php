<?php

namespace Tests\Feature;

use App\Models\Admin\Audio;
use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\Donation;
use App\Models\Admin\NotificationLog;
use App\Models\Admin\PaymentLog;
use App\Models\Admin\SankalpForm;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DiyaFlowTest extends TestCase
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
                    'id' => 'order_diya_'.$this->orders,
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'status' => 'created',
                ]);
            },
        ]);
    }

    public function test_guest_is_sent_to_login_and_returns_to_light_diya_after_otp(): void
    {
        config(['mail.default' => 'log']);
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'diya-user@example.test',
        ]);

        $this->get(route('light-diya'))
            ->assertRedirect(route('login'));

        $this->post(route('login.send-otp'), [
            'email' => $user->email,
        ])->assertRedirect();

        $otp = session('otp_flow.otp_preview');

        $this->post(route('login.verify-otp'), [
            'email' => $user->email,
            'otp' => $otp,
        ])->assertRedirect(route('light-diya'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_select_diya_deity_sankalp_and_donation_amount(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '51',
            ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Diya details saved. Complete Razorpay test payment to light your diya.',
                'redirect_url' => route('user.profile'),
            ])
            ->assertJsonPath('payment.order_id', 'order_diya_1')
            ->assertJsonPath('payment.amount', 5100)
            ->assertJsonStructure(['session_id', 'donation_id', 'payment_attempt_id', 'payment']);

        $session = DiyaSession::firstOrFail();
        $donation = Donation::firstOrFail();
        $attempt = PaymentAttempt::firstOrFail();
        $sankalp = SankalpForm::firstOrFail();

        $this->assertSame($user->id, $session->user_id);
        $this->assertSame($diya->id, $session->diya_id);
        $this->assertSame($deity->id, $session->deity_id);
        $this->assertSame('pending', $session->status);
        $this->assertSame('pending', $session->payment_status);
        $this->assertSame($sankalp->id, $session->sankalp_form_id);

        $this->assertSame('Aarav Sharma', $sankalp->full_name);
        $this->assertSame('Family Peace', $sankalp->purpose);
        $this->assertSame(51.0, (float) $donation->amount);
        $this->assertSame('pending', $donation->payment_status);
        $this->assertSame('diya', $donation->payment_purpose);
        $this->assertSame($session->id, $donation->session_id);

        $this->assertSame($donation->id, $attempt->donation_id);
        $this->assertSame($session->id, $attempt->payable_id);
        $this->assertSame(PaymentAttempt::STATUS_PROCESSING, $attempt->status);
        $this->assertSame('razorpay_test', $attempt->gateway);
        $this->assertSame('order_diya_1', $attempt->gateway_order_id);
        $this->assertSame(PaymentAttempt::PURPOSE_DONATION, $attempt->purpose);
        $this->assertSame($attempt->id, $session->latest_payment_attempt_id);
        $this->assertSame($attempt->id, $donation->latest_payment_attempt_id);
        $this->assertSame('order_diya_1', $donation->razorpay_order_id);
        $this->assertSame(1, PaymentLog::where('event_type', 'payment_hold_created')->count());
    }

    public function test_light_diya_page_renders_deity_image_data(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $deity->update(['featured_image' => 'assets/shiv.jpg']);

        $this->actingAs(User::factory()->create())
            ->get(route('light-diya'))
            ->assertOk()
            ->assertSee('deityPreviewImage')
            ->assertSee('assets\/shiv.jpg', false);
    }

    public function test_fixed_diya_uses_fixed_deity_even_if_request_has_other_deity(): void
    {
        $fixedDeity = Deity::create([
            'name' => 'Maa Durga',
            'slug' => 'maa-durga',
            'status' => 'active',
        ]);
        $otherDeity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);
        $diya = Diya::create([
            'name' => 'Durga Diya',
            'slug' => 'durga-diya',
            'seva_amount' => 108,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_FIXED,
            'fixed_deity_id' => $fixedDeity->id,
            'status' => 'active',
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $otherDeity->id,
                'selected_amount' => 'custom',
                'custom_amount' => '501',
            ]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame($fixedDeity->id, DiyaSession::firstOrFail()->deity_id);
        $this->assertSame(501.0, (float) Donation::firstOrFail()->amount);
        $this->assertSame($fixedDeity->id, PaymentAttempt::firstOrFail()->metadata['deity_id']);
    }

    public function test_successful_razorpay_test_payment_lights_the_diya(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '101',
            ]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_diya_1');

        $attempt = PaymentAttempt::firstOrFail();

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk()
            ->assertJson(['success' => true]);

        $session = DiyaSession::firstOrFail();
        $donation = Donation::firstOrFail();

        $this->assertSame(DiyaSession::STATUS_ACTIVE, $session->status);
        $this->assertSame('paid', $session->payment_status);
        $this->assertNotNull($session->start_at);
        $this->assertNotNull($session->end_at);
        $this->assertNotNull($session->expires_at);
        $this->assertSame('paid', $donation->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_PAID, $attempt->fresh()->status);
        $this->assertSame('pay_'.$attempt->id, $donation->fresh()->razorpay_payment_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'diya_payment_successful_'.$session->id,
        ]);
    }

    public function test_failed_razorpay_test_payment_does_not_light_the_diya(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '11',
            ]))
            ->assertOk();

        $attempt = PaymentAttempt::firstOrFail();

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.failure'), [
                'payment_attempt_id' => $attempt->id,
                'razorpay_order_id' => $attempt->gateway_order_id,
                'error' => ['description' => 'Test payment failed'],
            ])
            ->assertOk();

        $session = DiyaSession::firstOrFail();
        $donation = Donation::firstOrFail();

        $this->assertSame('pending', $session->status);
        $this->assertSame('pending', $session->payment_status);
        $this->assertNull($session->start_at);
        $this->assertSame('pending', $donation->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_FAILED, $attempt->fresh()->status);
    }

    public function test_failed_diya_payment_can_be_retried_without_new_session_or_sankalp(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '51',
            ]))
            ->assertOk();

        $firstAttempt = PaymentAttempt::firstOrFail();

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.failure'), [
                'payment_attempt_id' => $firstAttempt->id,
                'razorpay_order_id' => $firstAttempt->gateway_order_id,
                'error' => ['description' => 'Test payment failed'],
            ])
            ->assertOk();

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Retry Payment')
            ->assertSee('Akhand Diya')
            ->assertSee('Maa Lakshmi')
            ->assertSee('Rs.51.00')
            ->assertSee('Payment Status')
            ->assertSee('Diya Status')
            ->assertSee('Start Time')
            ->assertSee('End Time')
            ->assertSee('View Diya');

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'diya', 'id' => DiyaSession::firstOrFail()->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_diya_2');

        $this->assertDatabaseCount('diya_sessions', 1);
        $this->assertDatabaseCount('sankalp_forms', 1);
        $this->assertDatabaseCount('payment_attempts', 2);
        $this->assertDatabaseCount('donations', 2);
        $this->assertSame(PaymentAttempt::STATUS_FAILED, $firstAttempt->fresh()->status);
        $this->assertSame(PaymentAttempt::STATUS_PROCESSING, PaymentAttempt::latest('id')->first()->status);
        $this->assertSame(PaymentAttempt::latest('id')->first()->id, DiyaSession::firstOrFail()->latest_payment_attempt_id);
    }

    public function test_cancelled_diya_checkout_can_be_retried_without_new_session_or_sankalp(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '11',
            ]))
            ->assertOk();

        $firstAttempt = PaymentAttempt::firstOrFail();
        $session = DiyaSession::firstOrFail();

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'diya', 'id' => $session->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_diya_2');

        $this->assertDatabaseCount('diya_sessions', 1);
        $this->assertDatabaseCount('sankalp_forms', 1);
        $this->assertSame(PaymentAttempt::STATUS_CANCELLED, $firstAttempt->fresh()->status);
        $this->assertSame('pending', $session->fresh()->payment_status);
        $this->assertNull($session->fresh()->start_at);
    }

    public function test_razorpay_webhook_captures_diya_payment_and_ignores_duplicate(): void
    {
        config(['services.razorpay.webhook_secret' => 'test_webhook_secret']);

        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '101',
            ]))
            ->assertOk();

        $attempt = PaymentAttempt::firstOrFail();
        $body = $this->webhookBody($attempt);
        $signature = hash_hmac('sha256', $body, 'test_webhook_secret');

        $this->postWebhook($body, $signature)->assertOk()->assertJson(['success' => true]);
        $this->postWebhook($body, $signature)->assertOk()->assertJson(['success' => true]);

        $session = DiyaSession::firstOrFail();
        $donation = Donation::firstOrFail();

        $this->assertSame(DiyaSession::STATUS_ACTIVE, $session->status);
        $this->assertSame('paid', $session->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_PAID, $attempt->fresh()->status);
        $this->assertSame('paid', $donation->payment_status);
        $this->assertSame('pay_webhook_'.$attempt->id, $donation->razorpay_payment_id);
        $this->assertDatabaseCount('diya_sessions', 1);
        $this->assertDatabaseCount('sankalp_forms', 1);
        $this->assertSame(1, PaymentLog::where('event_type', 'payment_captured_webhook')->count());
    }

    public function test_razorpay_webhook_rejects_invalid_signature(): void
    {
        config(['services.razorpay.webhook_secret' => 'test_webhook_secret']);

        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
            ]))
            ->assertOk();

        $attempt = PaymentAttempt::firstOrFail();

        $this->postWebhook($this->webhookBody($attempt), 'bad-signature')
            ->assertForbidden();

        $this->assertSame('pending', DiyaSession::firstOrFail()->payment_status);
        $this->assertSame(PaymentAttempt::STATUS_PROCESSING, $attempt->fresh()->status);
    }

    public function test_diya_lifecycle_only_moves_paid_sessions(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();

        $future = $this->diyaSessionForLifecycle($user, $diya, $deity, [
            'status' => 'pending',
            'payment_status' => 'paid',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
        ]);

        $ready = $this->diyaSessionForLifecycle($user, $diya, $deity, [
            'status' => DiyaSession::STATUS_SCHEDULED,
            'payment_status' => 'paid',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
        ]);

        $ended = $this->diyaSessionForLifecycle($user, $diya, $deity, [
            'status' => DiyaSession::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
        ]);

        $unpaid = $this->diyaSessionForLifecycle($user, $diya, $deity, [
            'status' => 'pending',
            'payment_status' => 'pending',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
        ]);

        $this->artisan('diyas:sync-lifecycle')->assertExitCode(0);

        $this->assertSame(DiyaSession::STATUS_SCHEDULED, $future->fresh()->status);
        $this->assertSame(DiyaSession::STATUS_ACTIVE, $ready->fresh()->status);
        $this->assertSame(DiyaSession::STATUS_COMPLETED, $ended->fresh()->status);
        $this->assertSame('pending', $unpaid->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'message_type' => 'diya_started_'.$ready->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'message_type' => 'diya_completed_'.$ended->id,
        ]);
    }

    public function test_live_diya_wall_shows_only_paid_currently_active_diyas_without_private_details(): void
    {
        $user = User::factory()->create();
        $deity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);
        $visibleDiya = Diya::create([
            'name' => 'Visible Wall Diya',
            'slug' => 'visible-wall-diya',
            'seva_amount' => 51,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'status' => 'active',
        ]);
        $hiddenDiya = Diya::create([
            'name' => 'Hidden Wall Diya',
            'slug' => 'hidden-wall-diya',
            'seva_amount' => 51,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'status' => 'active',
        ]);

        $visibleSankalp = SankalpForm::create([
            'user_id' => $user->id,
            'full_name' => 'Private Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Private Sankalp',
        ]);

        DiyaSession::create([
            'user_id' => $user->id,
            'diya_id' => $visibleDiya->id,
            'deity_id' => $deity->id,
            'sankalp_form_id' => $visibleSankalp->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
            'status' => DiyaSession::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
        ]);

        DiyaSession::create([
            'user_id' => $user->id,
            'diya_id' => $hiddenDiya->id,
            'deity_id' => $deity->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
            'status' => DiyaSession::STATUS_ACTIVE,
            'payment_status' => 'pending',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
        ]);

        DiyaSession::create([
            'user_id' => $user->id,
            'diya_id' => $hiddenDiya->id,
            'deity_id' => $deity->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
            'status' => DiyaSession::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Visible Wall Diya')
            ->assertSee('Maa Lakshmi')
            ->assertDontSee('Hidden Wall Diya')
            ->assertDontSee('Private Devotee')
            ->assertDontSee('9999999999')
            ->assertDontSee('Private Sankalp');
    }

    public function test_diya_session_page_uses_selected_deity_theme(): void
    {
        $user = User::factory()->create();

        $deityWithAudio = function (string $name, string $slug, string $mantra, string $ambient, array $theme) {
            $deity = Deity::create($theme + ['name' => $name, 'slug' => $slug, 'status' => 'active']);
            $mantraAudio = Audio::create(['deity_id' => $deity->id, 'title' => $mantra, 'slug' => $slug.'-mantra', 'category' => 'mantra', 'audio_file' => 'audio/'.$slug.'-mantra.mp3', 'status' => 'active']);
            $ambientAudio = Audio::create(['deity_id' => $deity->id, 'title' => $ambient, 'slug' => $slug.'-ambient', 'category' => 'temple_ambience', 'audio_file' => 'audio/'.$slug.'-ambient.mp3', 'status' => 'active']);
            $deity->update(['mantra_audio_id' => $mantraAudio->id, 'ambient_audio_id' => $ambientAudio->id]);

            return [$deity, $mantraAudio, $ambientAudio];
        };

        [$ganesh] = $deityWithAudio('Shree Ganesh', 'shree-ganesh', 'Ganesh Mantra', 'Temple Bells', [
            'temple_background_image' => 'deities/theme-backgrounds/ganesh.png',
            'primary_color' => '#f97316',
            'secondary_color' => '#facc15',
            'glow_color' => '#fed7aa',
            'particle_style' => 'divine_light',
            'flame_style' => 'orange',
        ]);
        [$shiv] = $deityWithAudio('Lord Shiv', 'lord-shiv', 'Shiv Mantra', 'Mountain Wind', [
            'primary_color' => '#2563eb',
            'secondary_color' => '#94a3b8',
            'glow_color' => '#bfdbfe',
            'particle_style' => 'smoke',
            'flame_style' => 'blue',
        ]);
        [, $lakshmiMantra] = $deityWithAudio('Maa Lakshmi', 'maa-lakshmi', 'Lakshmi Mantra', 'Lakshmi Ambience', []);

        $selectDiya = Diya::create([
            'name' => 'Select Deep',
            'slug' => 'select-deep',
            'seva_amount' => 108,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'mantra_audio_id' => $lakshmiMantra->id,
            'status' => 'active',
        ]);
        $fixedDiya = Diya::create([
            'name' => 'Fixed Shiv Deep',
            'slug' => 'fixed-shiv-deep',
            'seva_amount' => 108,
            'duration' => '2 hours',
            'deity_selection_mode' => Diya::MODE_FIXED,
            'fixed_deity_id' => $shiv->id,
            'mantra_audio_id' => $lakshmiMantra->id,
            'status' => 'active',
        ]);

        $ganeshSession = $this->paidDiyaSession($user, $selectDiya, $ganesh, 51);
        $shivSession = $this->paidDiyaSession($user, $selectDiya, $shiv, 101);
        $fixedSession = $this->paidDiyaSession($user, $fixedDiya, $shiv, 108);

        $this->actingAs($user)
            ->get(route('diya.session', $ganeshSession))
            ->assertOk()
            ->assertSee('deity-particles-divine_light', false)
            ->assertSee('deity-flame-orange', false)
            ->assertSee('--diya-primary: #f97316', false)
            ->assertSee('storage/deities/theme-backgrounds/ganesh.png', false)
            ->assertSee('Select Deep')
            ->assertSee('Shree Ganesh')
            ->assertSee('Aarav Sharma')
            ->assertSee('Family Peace')
            ->assertSee('Rs.51.00')
            ->assertSee('Payment</small>Paid', false)
            ->assertSee('Status</small>Active', false)
            ->assertSee('Ganesh Mantra')
            ->assertSee('Temple Bells')
            ->assertDontSee('Lakshmi Mantra');

        $this->actingAs($user)
            ->get(route('diya.session', $shivSession))
            ->assertOk()
            ->assertSee('deity-particles-smoke', false)
            ->assertSee('deity-flame-blue', false)
            ->assertSee('--diya-primary: #2563eb', false)
            ->assertSee('Select Deep')
            ->assertSee('Lord Shiv')
            ->assertSee('Shiv Mantra')
            ->assertSee('Mountain Wind')
            ->assertSee('Rs.101.00')
            ->assertDontSee('Lakshmi Mantra');

        $this->actingAs($user)
            ->get(route('diya.session', $fixedSession))
            ->assertOk()
            ->assertSee('Fixed Shiv Deep')
            ->assertSee('Lord Shiv')
            ->assertSee('Shiv Mantra')
            ->assertSee('Mountain Wind')
            ->assertSee('Rs.108.00')
            ->assertDontSee('Lakshmi Mantra');
    }

    public function test_unpaid_diya_session_uses_safe_theme_defaults_and_does_not_show_active_audio(): void
    {
        [$diya, $deity] = $this->userSelectDiya();
        $user = User::factory()->create();
        $session = $this->diyaSessionForLifecycle($user, $diya, $deity, [
            'status' => 'pending',
            'payment_status' => 'pending',
            'admin_note' => json_encode(['donation_amount' => 11]),
        ]);

        $this->actingAs($user)
            ->get(route('diya.session', $session))
            ->assertOk()
            ->assertSee('deity-particles-golden_sparkles', false)
            ->assertSee('deity-flame-normal', false)
            ->assertSee('--diya-primary: #c78d22', false)
            ->assertSee('Payment Pending')
            ->assertSee('Mantra and ambient sound will be available after payment is completed.')
            ->assertDontSee('<audio id=', false);
    }

    public function test_donation_amount_is_validated_on_backend(): void
    {
        [$diya, $deity] = $this->userSelectDiya();

        $this->actingAs(User::factory()->create())
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => '999',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('selected_amount');

        $this->actingAs(User::factory()->create())
            ->postJson(route('diya.store'), $this->payload([
                'diya_id' => $diya->id,
                'deity_id' => $deity->id,
                'selected_amount' => 'custom',
                'custom_amount' => '',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('custom_amount');

        $this->assertDatabaseCount('diya_sessions', 0);
        $this->assertDatabaseCount('donations', 0);
        $this->assertDatabaseCount('payment_attempts', 0);
    }

    private function userSelectDiya(): array
    {
        $deity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);

        $diya = Diya::create([
            'name' => 'Akhand Diya',
            'slug' => 'akhand-diya',
            'short_description' => 'Sacred diya offering',
            'seva_amount' => 108,
            'duration' => '1 day',
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'status' => 'active',
        ]);

        return [$diya, $deity];
    }

    private function payload(array $changes = []): array
    {
        return array_merge([
            'diya_id' => null,
            'deity_id' => null,
            'full_name' => 'Aarav Sharma',
            'mobile' => '9876543210',
            'gotra' => 'Kashyap',
            'dob' => '1992-04-15',
            'birth_time' => '06:30',
            'birth_place' => 'Varanasi',
            'father_name' => 'Ramesh Sharma',
            'mother_name' => 'Sita Sharma',
            'spouse_name' => 'Priya Sharma',
            'family_names' => 'Anaya, Kabir',
            'purpose' => 'Family Peace',
            'mannokamna' => 'Peace and good health',
            'selected_amount' => '11',
            'custom_amount' => null,
            'consent' => '1',
        ], $changes);
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

    private function webhookBody(PaymentAttempt $attempt): string
    {
        return json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_webhook_'.$attempt->id,
                        'order_id' => $attempt->gateway_order_id,
                        'status' => 'captured',
                    ],
                ],
            ],
        ]);
    }

    private function postWebhook(string $body, string $signature)
    {
        return $this->call('POST', route('payments.razorpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ], $body);
    }

    private function diyaSessionForLifecycle(User $user, Diya $diya, Deity $deity, array $values): DiyaSession
    {
        return DiyaSession::create(array_merge([
            'user_id' => $user->id,
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
        ], $values));
    }

    private function paidDiyaSession(User $user, Diya $diya, Deity $deity, float $amount): DiyaSession
    {
        $sankalp = SankalpForm::create([
            'user_id' => $user->id,
            'full_name' => 'Aarav Sharma',
            'mobile' => '9876543210',
            'purpose' => 'Family Peace',
        ]);

        $session = DiyaSession::create([
            'user_id' => $user->id,
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'sankalp_form_id' => $sankalp->id,
            'booking_date' => now()->toDateString(),
            'slot' => 'Instant Diya Offering',
            'status' => DiyaSession::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addDay(),
            'expires_at' => now()->addDay(),
            'admin_note' => json_encode([
                'diya_name' => $diya->name,
                'deity_name' => $deity->name,
                'donation_amount' => $amount,
            ]),
        ]);

        $donation = Donation::create([
            'user_id' => $user->id,
            'payment_purpose' => 'diya',
            'session_type' => DiyaSession::class,
            'session_id' => $session->id,
            'amount' => $amount,
            'currency' => 'INR',
            'donor_name' => $sankalp->full_name,
            'donor_mobile' => $sankalp->mobile,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'donation_id' => $donation->id,
            'payable_type' => DiyaSession::class,
            'payable_id' => $session->id,
            'purpose' => PaymentAttempt::PURPOSE_DONATION,
            'gateway' => 'razorpay_test',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => PaymentAttempt::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $donation->update(['latest_payment_attempt_id' => $attempt->id]);
        $session->update(['latest_payment_attempt_id' => $attempt->id]);

        return $session;
    }
}
