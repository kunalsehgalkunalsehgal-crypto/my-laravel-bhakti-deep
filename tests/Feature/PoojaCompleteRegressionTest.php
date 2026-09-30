<?php

namespace Tests\Feature;

use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditAvailabilitySetting;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditService;
use App\Models\PaymentAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final Pooja regression/contract test.
 *
 * Purpose:
 * - Live Pooja remains Pandit/date/slot based.
 * - Digital Pooja is completely separate.
 * - Digital uses admin price + snapshotted media.
 * - Digital never creates Zoom/Pandit payout.
 * - Digital payment retry does not require a Pandit.
 * - Admin-controlled Digital access duration is snapshotted and enforced.
 * - Legacy NULL pooja_type continues to behave as Live.
 *
 * Run only this file:
 * php artisan test tests/Feature/PoojaCompleteRegressionTest.php
 */
class PoojaCompleteRegressionTest extends TestCase
{
    use RefreshDatabase;

    private int $razorpayOrders = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.timezone' => 'Asia/Kolkata',
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => function ($request) {
                $this->razorpayOrders++;

                return Http::response([
                    'id' => 'order_pooja_audit_'.$this->razorpayOrders,
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'status' => 'created',
                ]);
            },
            '*' => Http::response([], 200),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_required_pooja_schema_for_final_live_and_digital_flow_exists(): void
    {
        $this->assertColumns('poojas', [
            'live_pooja_enabled',
            'live_pooja_title',
            'live_pooja_description',
            'live_pooja_price',
            'digital_pooja_enabled',
            'digital_pooja_title',
            'digital_pooja_description',
            'digital_pooja_price',
            'digital_pooja_video',
            'digital_pooja_audio_id',
            'digital_pooja_access_minutes',
        ]);

        $this->assertColumns('pooja_sessions', [
            'pooja_type',
            'pooja_type_title',
            'pooja_type_price',
            'digital_video_path',
            'digital_audio_path',
            'digital_access_minutes',
            'start_at',
            'expires_at',
        ]);
    }

    public function test_booking_page_has_live_and_digital_and_old_digital_blocker_is_gone(): void
    {
        $this->requireCoreDigitalSchema();
        $this->createPooja();

        $response = $this->get('/book-pooja/final-regression-pooja');

        $response->assertOk()
            ->assertSee('Live Pooja')
            ->assertSee('Digital Pooja')
            ->assertDontSee('Digital Pooja booking will be available soon.');

        $blade = file_get_contents(resource_path('views/pages/pooja-booking-show.blade.php'));

        $this->assertStringNotContainsString(
            'Digital Pooja booking will be available soon.',
            $blade,
            'Old Digital frontend blocker still exists in pooja-booking-show.blade.php.'
        );
    }

    public function test_digital_booking_succeeds_without_pandit_mode_date_or_slot_and_uses_admin_price(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('digital-booking@example.test');
        $pooja = $this->createPooja(digitalPrice: 777);

        $response = $this->actingAs($user)
            ->postJson(route('pooja.store'), $this->digitalPayload($pooja, clientAmount: 1));

        $response->assertOk()->assertJson(['success' => true]);

        $session = PoojaSession::query()->latest('id')->firstOrFail();

        $this->assertSame('digital', $session->pooja_type);
        $this->assertNull($session->pandit_id);
        $this->assertNull($session->pandit_service_id);
        $this->assertNull($session->booking_mode);
        $this->assertNull($session->booking_date);
        $this->assertNull($session->slot);
        $this->assertNull($session->slot_start_time);
        $this->assertNull($session->slot_end_time);
        $this->assertSame(777.0, (float) $session->pooja_type_price);
        $this->assertSame('poojas/videos/final-digital.mp4', $session->digital_video_path);
        $this->assertSame('audio/final-mantra.mp3', $session->digital_audio_path);

        $attempt = PaymentAttempt::query()
            ->where('payable_type', PoojaSession::class)
            ->where('payable_id', $session->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(777.0, (float) $attempt->amount, 'Digital payment trusted client amount instead of admin price.');
        $this->assertSame(PaymentAttempt::PURPOSE_BOOKING, $attempt->purpose);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://api.razorpay.com/v1/orders'
            && (int) $request['amount'] === 77700
        );
    }

    public function test_disabled_digital_type_cannot_be_booked_by_manual_post(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('digital-disabled@example.test');
        $pooja = $this->createPooja(digitalEnabled: false);

        $response = $this->actingAs($user)
            ->postJson(route('pooja.store'), $this->digitalPayload($pooja));

        $response->assertUnprocessable();
        $this->assertDatabaseCount('pooja_sessions', 0);
    }

    public function test_digital_payment_retry_after_expired_hold_does_not_require_pandit(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('digital-retry@example.test');
        $pooja = $this->createPooja();
        [$session, $attempt] = $this->startDigitalBooking($user, $pooja);

        $session->update(['payment_hold_expires_at' => now()->subMinute()]);
        $attempt->update(['hold_expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->postJson(route('payments.bookings.retry', ['type' => 'pooja', 'id' => $session->id]))
            ->assertOk()
            ->assertJsonPath('payment.order_id', 'order_pooja_audit_2');

        $this->assertNull($session->fresh()->pandit_id);
        $this->assertTrue($session->fresh()->payment_hold_expires_at->isFuture());
        $this->assertSame(2, PaymentAttempt::where('payable_type', PoojaSession::class)->where('payable_id', $session->id)->count());
    }

    public function test_digital_payment_success_activates_for_snapshotted_admin_duration_and_creates_no_zoom_or_payout(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $user = $this->user('digital-paid@example.test');
        $pooja = $this->createPooja(accessMinutes: 120);
        [$session, $attempt] = $this->startDigitalBooking($user, $pooja);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk()
            ->assertJson(['success' => true]);

        $session->refresh();

        $this->assertSame('digital', $session->pooja_type);
        $this->assertSame('paid', $session->payment_status);
        $this->assertSame('active', $session->status);
        $this->assertSame(120, (int) $session->digital_access_minutes);
        $this->assertNotNull($session->start_at);
        $this->assertNotNull($session->expires_at);
        $this->assertSame(7200, $session->start_at->diffInSeconds($session->expires_at));
        $this->assertDatabaseMissing('video_meetings', [
            'session_type' => PoojaSession::class,
            'session_id' => $session->id,
        ]);
        $this->assertDatabaseMissing('pandit_payouts', [
            'session_type' => PoojaSession::class,
            'session_id' => $session->id,
        ]);
    }

    public function test_existing_paid_digital_session_keeps_original_duration_if_admin_changes_pooja_later(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $user = $this->user('duration-snapshot@example.test');
        $pooja = $this->createPooja(accessMinutes: 60);
        [$session, $attempt] = $this->startDigitalBooking($user, $pooja);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        $session->refresh();
        $originalExpiry = $session->expires_at?->copy();

        $pooja->forceFill(['digital_pooja_access_minutes' => 180])->save();
        $session->refresh();

        $this->assertSame(60, (int) $session->digital_access_minutes);
        $this->assertTrue($session->expires_at->equalTo($originalExpiry));
        $this->assertSame(3600, $session->start_at->diffInSeconds($session->expires_at));
    }

    public function test_paid_digital_owner_can_access_before_expiry_but_another_user_cannot(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $owner = $this->user('digital-owner@example.test');
        $other = $this->user('digital-other@example.test');
        $pooja = $this->createPooja(accessMinutes: 120);
        [$session, $attempt] = $this->startDigitalBooking($owner, $pooja);

        $this->actingAs($owner)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-09-28 11:59:00', 'Asia/Kolkata'));

        $this->actingAs($owner)
            ->get('/digital-pooja/'.$session->id)
            ->assertOk()
            ->assertSee('Pooja Shuru Kare');

        $this->actingAs($other)
            ->get('/digital-pooja/'.$session->id)
            ->assertForbidden();
    }

    public function test_digital_access_is_blocked_after_admin_duration_expires(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $owner = $this->user('digital-expiry@example.test');
        $pooja = $this->createPooja(accessMinutes: 120);
        [$session, $attempt] = $this->startDigitalBooking($owner, $pooja);

        $this->actingAs($owner)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-09-28 12:00:01', 'Asia/Kolkata'));

        $response = $this->actingAs($owner)->get('/digital-pooja/'.$session->id);

        if ($response->status() === 200) {
            $response
                ->assertDontSee('Pooja Shuru Kare')
                ->assertDontSee('poojas/videos/final-digital.mp4')
                ->assertDontSee('audio/final-mantra.mp3');
        } else {
            $this->assertContains($response->status(), [403, 410], 'Expired Digital Pooja must be blocked.');
        }

        $session->refresh();
        $this->assertSame('completed', $session->status, 'Expired Digital Pooja was not marked completed.');
        $this->assertNotNull($session->completed_at);
    }

    public function test_digital_player_does_not_expose_raw_public_storage_media_urls(): void
    {
        $bladePath = resource_path('views/pages/digital-pooja.blade.php');
        $this->assertFileExists($bladePath);

        $blade = file_get_contents($bladePath);

        $this->assertDoesNotMatchRegularExpression(
            '/asset\s*\([^\)]*digital_video_path/si',
            $blade,
            'Digital video is still exposed through a raw public storage asset URL.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/asset\s*\([^\)]*digital_audio_path/si',
            $blade,
            'Digital mantra audio is still exposed through a raw public storage asset URL.'
        );
    }

    public function test_digital_pooja_is_not_available_through_live_or_zoom_session_routes(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $user = $this->user('digital-live-block@example.test');
        $pooja = $this->createPooja();
        [$session, $attempt] = $this->startDigitalBooking($user, $pooja);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        $liveResponse = $this->actingAs($user)
            ->get(route('live.session', ['type' => 'pooja', 'id' => $session->id]));

        $this->assertContains($liveResponse->status(), [403, 404]);

        $sdkResponse = $this->actingAs($user)
            ->postJson(route('live.session.sdk', ['type' => 'pooja', 'id' => $session->id]));

        $this->assertContains($sdkResponse->status(), [403, 404, 422]);
        $this->assertDatabaseMissing('video_meetings', [
            'session_type' => PoojaSession::class,
            'session_id' => $session->id,
        ]);
    }

    public function test_digital_pooja_is_excluded_from_user_live_session_lists(): void
    {
        $this->requireDurationSchema();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Kolkata'));

        $user = $this->user('digital-list@example.test');
        $pooja = $this->createPooja();
        [$session, $attempt] = $this->startDigitalBooking($user, $pooja);

        $this->actingAs($user)
            ->postJson(route('payments.razorpay.verify'), $this->successPayload($attempt))
            ->assertOk();

        $this->actingAs($user)
            ->get('/live-sessions/pooja')
            ->assertOk()
            ->assertDontSee('/live-sessions/pooja/'.$session->id, false);
    }

    public function test_live_online_pooja_still_books_with_pandit_date_and_slot(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('live-online@example.test');
        $pooja = $this->createPooja();
        [$pandit, $service, $date] = $this->livePanditFixtures($pooja);

        $booking = $this->liveBookingSession($pooja, $pandit, $service, $date, 'online');

        $this->actingAs($user)
            ->withSession(['pooja_booking' => $booking])
            ->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertOk()
            ->assertJson(['success' => true]);

        $session = PoojaSession::query()->latest('id')->firstOrFail();

        $this->assertSame('live', $session->pooja_type);
        $this->assertSame('online', $session->booking_mode);
        $this->assertSame($pandit->id, $session->pandit_id);
        $this->assertSame($date->toDateString(), $session->booking_date?->toDateString());
        $this->assertSame('7:00 AM - 8:00 AM', $session->slot);
        $this->assertNull($session->digital_video_path);
        $this->assertNull($session->digital_audio_path);
    }

    public function test_live_offline_pooja_still_books_with_location_pandit_date_and_slot(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('live-offline@example.test');
        $pooja = $this->createPooja();
        [$pandit, $service, $date] = $this->livePanditFixtures($pooja, offline: true);

        $booking = $this->liveBookingSession($pooja, $pandit, $service, $date, 'offline') + [
            'state' => 'Haryana',
            'city' => 'Ambala',
        ];

        $this->actingAs($user)
            ->withSession(['pooja_booking' => $booking])
            ->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertOk()
            ->assertJson(['success' => true]);

        $session = PoojaSession::query()->latest('id')->firstOrFail();

        $this->assertSame('live', $session->pooja_type);
        $this->assertSame('offline', $session->booking_mode);
        $this->assertSame('Haryana', $session->state);
        $this->assertSame('Ambala', $session->city);
        $this->assertSame($pandit->id, $session->pandit_id);
        $this->assertSame('7:00 AM - 8:00 AM', $session->slot);
    }

    public function test_legacy_null_pooja_type_is_still_treated_as_live_in_live_listing(): void
    {
        $this->requireCoreDigitalSchema();

        $user = $this->user('legacy-live@example.test');

        $session = PoojaSession::query()->forceCreate([
            'user_id' => $user->id,
            'service_type' => 'pooja',
            'pooja_type' => null,
            'pooja_type_title' => 'Legacy Live Pooja',
            'booking_mode' => 'online',
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get('/live-sessions/pooja')
            ->assertOk()
            ->assertSee('/live-sessions/pooja/'.$session->id, false);
    }

    private function startDigitalBooking(User $user, Pooja $pooja): array
    {
        $response = $this->actingAs($user)
            ->postJson(route('pooja.store'), $this->digitalPayload($pooja));

        $response->assertOk()->assertJson(['success' => true]);

        $session = PoojaSession::query()->latest('id')->firstOrFail();
        $attempt = PaymentAttempt::query()
            ->where('payable_type', PoojaSession::class)
            ->where('payable_id', $session->id)
            ->latest('id')
            ->firstOrFail();

        return [$session, $attempt];
    }

    private function createPooja(
        bool $liveEnabled = true,
        bool $digitalEnabled = true,
        int $digitalPrice = 501,
        int $accessMinutes = 120
    ): Pooja {
        $this->requireCoreDigitalSchema();

        $audioId = DB::table('audio_library')->insertGetId([
            'title' => 'Final Regression Mantra',
            'slug' => 'final-regression-mantra-'.uniqid(),
            'category' => 'mantra',
            'audio_file' => 'audio/final-mantra.mp3',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attributes = [
            'name' => 'Final Regression Pooja',
            'slug' => 'final-regression-pooja',
            'short_description' => 'Regression test pooja',
            'base_price' => 999,
            'duration' => '60 min',
            'mode' => 'Live + Digital',
            'available_slots' => ['7:00 AM - 8:00 AM'],
            'status' => 'active',
            'live_pooja_enabled' => $liveEnabled,
            'live_pooja_title' => 'Live Pooja',
            'live_pooja_description' => 'Live with Pandit',
            'live_pooja_price' => 1001,
            'digital_pooja_enabled' => $digitalEnabled,
            'digital_pooja_title' => 'Digital Pooja',
            'digital_pooja_description' => 'Video and mantra experience',
            'digital_pooja_price' => $digitalPrice,
            'digital_pooja_video' => 'poojas/videos/final-digital.mp4',
            'digital_pooja_audio_id' => $audioId,
        ];

        if (Schema::hasColumn('poojas', 'digital_pooja_access_minutes')) {
            $attributes['digital_pooja_access_minutes'] = $accessMinutes;
        }

        return Pooja::query()->forceCreate($attributes);
    }

    private function digitalPayload(Pooja $pooja, int $clientAmount = 1): array
    {
        return [
            'pooja_slug' => $pooja->slug,
            'pooja_type' => 'digital',
            'package_name' => 'Digital Pooja',
            'package_amount' => $clientAmount,
            'full_name' => 'Digital Devotee',
            'gotra' => 'Kashyap',
            'mobile' => '9999999999',
            'purpose' => 'Peace and prosperity',
            'mannokamna' => 'Family wellbeing',
            'donation_amount' => 0,
            'otp' => '123456',
        ];
    }

    private function livePayload(Pooja $pooja, Carbon $date): array
    {
        return [
            'pooja_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'package_name' => 'Live Pooja',
            'package_amount' => 1,
            'full_name' => 'Live Devotee',
            'mobile' => '8888888888',
            'purpose' => 'Prosperity',
            'donation_amount' => 0,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ];
    }

    private function livePanditFixtures(Pooja $pooja, bool $offline = false): array
    {
        $date = Carbon::tomorrow('Asia/Kolkata');

        $pandit = Pandit::create([
            'full_name' => $offline ? 'Offline Pooja Pandit' : 'Online Pooja Pandit',
            'pandit_name' => $offline ? 'Offline Pooja Pandit' : 'Online Pooja Pandit',
            'email' => ($offline ? 'offline' : 'online').'-pooja-pandit@example.test',
            'status' => 'verified',
        ]);

        $service = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'pooja',
            'service_name' => $pooja->name,
            'pooja_id' => $pooja->id,
            'duration_minutes' => 60,
            'status' => 'approved',
        ]);

        PanditOnlineSetup::create([
            'pandit_id' => $pandit->id,
            'online_pooja' => true,
            'stable_internet' => true,
        ]);

        PanditAvailabilitySetting::create([
            'pandit_id' => $pandit->id,
            'accept_new_bookings' => true,
            'offline_pooja' => $offline,
            'service_state' => $offline ? 'Haryana' : null,
            'service_city' => $offline ? 'Ambala' : null,
        ]);

        PanditAvailabilitySlot::create([
            'pandit_id' => $pandit->id,
            'day' => $date->format('l'),
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'is_available' => true,
        ]);

        return [$pandit, $service, $date];
    }

    private function liveBookingSession(
        Pooja $pooja,
        Pandit $pandit,
        PanditService $service,
        Carbon $date,
        string $bookingMode
    ): array {
        return [
            'service_type' => 'pooja',
            'service_id' => $pooja->id,
            'service_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'pooja_type_title' => 'Live Pooja',
            'pooja_type_price' => 1001,
            'pandit_service_id' => $service->id,
            'pandit_id' => $pandit->id,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Live Pooja',
            'booking_mode' => $bookingMode,
        ];
    }

    private function successPayload(PaymentAttempt $attempt): array
    {
        $paymentId = 'pay_pooja_audit_'.$attempt->id;

        return [
            'payment_attempt_id' => $attempt->id,
            'razorpay_order_id' => $attempt->gateway_order_id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => hash_hmac(
                'sha256',
                $attempt->gateway_order_id.'|'.$paymentId,
                'rzp_test_secret'
            ),
        ];
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Pooja Regression User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function requireCoreDigitalSchema(): void
    {
        $required = [
            'poojas' => [
                'live_pooja_enabled',
                'live_pooja_price',
                'digital_pooja_enabled',
                'digital_pooja_price',
                'digital_pooja_video',
                'digital_pooja_audio_id',
            ],
            'pooja_sessions' => [
                'pooja_type',
                'pooja_type_title',
                'pooja_type_price',
                'digital_video_path',
                'digital_audio_path',
            ],
        ];

        foreach ($required as $table => $columns) {
            $this->assertColumns($table, $columns);
        }
    }

    private function requireDurationSchema(): void
    {
        $this->requireCoreDigitalSchema();
        $this->assertColumns('poojas', ['digital_pooja_access_minutes']);
        $this->assertColumns('pooja_sessions', ['digital_access_minutes', 'start_at', 'expires_at']);
    }

    private function assertColumns(string $table, array $columns): void
    {
        $this->assertTrue(Schema::hasTable($table), "Required table [{$table}] does not exist.");

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn($table, $column),
                "Missing required column [{$table}.{$column}]. The related Pooja migration/feature is not implemented."
            );
        }
    }
}
