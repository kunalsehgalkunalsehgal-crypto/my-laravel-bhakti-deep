<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditAvailabilitySetting;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditService;
use App\Models\User;
use App\Models\VideoMeeting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PoojaFinalRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_both_live_only_and_digital_only_configurations(): void
    {
        $role = AdminRole::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'status' => 'active']);
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'final-pooja-admin@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        foreach ([
            'both-types' => [true, true],
            'live-only' => [true, false],
            'digital-only' => [false, true],
        ] as $slug => [$liveEnabled, $digitalEnabled]) {
            $payload = [
                'name' => str($slug)->headline()->toString(),
                'slug' => $slug,
                'base_price' => 1001,
                'live_pooja_title' => 'Live Pooja',
                'live_pooja_description' => 'Live description.',
                'live_pooja_price' => 1501,
                'digital_pooja_title' => 'Digital Pooja',
                'digital_pooja_description' => 'Digital description.',
                'digital_pooja_price' => 751,
                'mode' => 'Live + Replay',
                'status' => 'active',
            ];

            if ($liveEnabled) {
                $payload['live_pooja_enabled'] = '1';
            }

            if ($digitalEnabled) {
                $payload['digital_pooja_enabled'] = '1';
            }

            $this->actingAs($admin, 'admin')
                ->post(route('admin.poojas.store'), $payload)
                ->assertRedirect(route('admin.poojas.index'));

            $pooja = Pooja::where('slug', $slug)->firstOrFail();

            $this->assertSame($liveEnabled, $pooja->live_pooja_enabled);
            $this->assertSame($digitalEnabled, $pooja->digital_pooja_enabled);
        }
    }

    public function test_disabled_pooja_types_cannot_be_booked_manually(): void
    {
        $this->actingAs(User::factory()->create());

        $digitalOnly = $this->pooja('digital-only-manual', false, true);
        $this->postJson(route('pooja.store'), $this->livePayload($digitalOnly))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pooja_type');

        $liveOnly = $this->pooja('live-only-manual', true, false);
        $this->postJson(route('pooja.store'), [
            'pooja_slug' => $liveOnly->slug,
            'pooja_type' => 'digital',
            'full_name' => 'Manual Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
        ])->assertUnprocessable()->assertJsonValidationErrors('pooja_type');

        $this->assertDatabaseCount('pooja_sessions', 0);
    }

    public function test_live_pooja_requires_date_slot_pandit_and_offline_location(): void
    {
        [$user, $pooja, $pandit, $service, $date] = $this->liveFixtures();

        $this->actingAs($user)
            ->postJson(route('pooja.store'), array_diff_key($this->livePayload($pooja, $date), array_flip(['booking_date', 'slot'])))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['booking_date', 'slot']);

        $this->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertUnprocessable()
            ->assertJson(['message' => 'Please select a pandit before payment.']);

        $this->withSession(['pooja_booking' => $this->bookingSession($pooja, $pandit, $service, $date, 'offline')])
            ->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('city');
    }

    public function test_live_pooja_rejects_an_overlapping_pandit_booking(): void
    {
        [$user, $pooja, $pandit, $service, $date] = $this->liveFixtures();

        PoojaSession::create([
            'user_id' => $user->id,
            'service_type' => 'pooja',
            'ritual_id' => $pooja->id,
            'ritual_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'pandit_id' => $pandit->id,
            'pandit_service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->withSession(['pooja_booking' => $this->bookingSession($pooja, $pandit, $service, $date, 'online')])
            ->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slot');

        $this->assertDatabaseCount('pooja_sessions', 1);
    }

    public function test_paid_live_pooja_creates_zoom_meeting_after_pandit_accepts(): void
    {
        $provider = new FinalRegressionVideoMeetingProvider();
        $this->app->instance(VideoMeetingProvider::class, $provider);
        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_final_live',
                'amount' => 150100,
                'currency' => 'INR',
                'status' => 'created',
            ]),
        ]);

        [$user, $pooja, $pandit, $service, $date] = $this->liveFixtures();

        $this->actingAs($user)
            ->withSession(['pooja_booking' => $this->bookingSession($pooja, $pandit, $service, $date, 'online')])
            ->postJson(route('pooja.store'), $this->livePayload($pooja, $date))
            ->assertOk();

        $session = PoojaSession::firstOrFail();
        $session->update(['status' => 'scheduled', 'payment_status' => 'paid']);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'pooja', 'id' => $session->id]))
            ->assertRedirect();

        $this->assertSame('confirmed', $session->fresh()->status);
        $this->assertSame(1, $provider->calls);
        $this->assertDatabaseHas('video_meetings', [
            'session_type' => PoojaSession::class,
            'session_id' => $session->id,
            'provider' => 'fake',
        ]);
    }

    private function liveFixtures(): array
    {
        $user = User::factory()->create();
        $date = Carbon::tomorrow('Asia/Kolkata');
        $pooja = $this->pooja('final-live-pooja', true, true);
        $pandit = Pandit::create([
            'full_name' => 'Final Pooja Pandit',
            'pandit_name' => 'Final Pooja Pandit',
            'email' => 'final-pooja-pandit@example.test',
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

        PanditOnlineSetup::create(['pandit_id' => $pandit->id, 'online_pooja' => true]);
        PanditAvailabilitySetting::create([
            'pandit_id' => $pandit->id,
            'accept_new_bookings' => true,
            'offline_pooja' => true,
            'service_state' => 'Punjab',
            'service_city' => 'Lalru',
        ]);
        PanditAvailabilitySlot::create([
            'pandit_id' => $pandit->id,
            'day' => $date->format('l'),
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'is_available' => true,
        ]);

        return [$user, $pooja, $pandit, $service, $date];
    }

    private function pooja(string $slug, bool $live, bool $digital): Pooja
    {
        return Pooja::create([
            'name' => str($slug)->headline()->toString(),
            'slug' => $slug,
            'base_price' => 1001,
            'live_pooja_enabled' => $live,
            'live_pooja_title' => 'Live Pooja',
            'live_pooja_price' => 1501,
            'digital_pooja_enabled' => $digital,
            'digital_pooja_title' => 'Digital Pooja',
            'digital_pooja_price' => 751,
            'status' => 'active',
        ]);
    }

    private function livePayload(Pooja $pooja, ?Carbon $date = null): array
    {
        return [
            'pooja_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'package_name' => 'Live Pooja',
            'full_name' => 'Live Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
            'booking_date' => ($date ?: Carbon::tomorrow('Asia/Kolkata'))->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
        ];
    }

    private function bookingSession(Pooja $pooja, Pandit $pandit, PanditService $service, Carbon $date, string $mode): array
    {
        return [
            'service_type' => 'pooja',
            'service_id' => $pooja->id,
            'service_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'pandit_service_id' => $service->id,
            'pandit_id' => $pandit->id,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Live Pooja',
            'booking_mode' => $mode,
        ];
    }
}

class FinalRegressionVideoMeetingProvider implements VideoMeetingProvider
{
    public int $calls = 0;

    public function providerName(): string
    {
        return 'fake';
    }

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array
    {
        $this->calls++;

        return [
            'provider' => 'fake',
            'external_meeting_id' => 'final-live-'.$this->calls,
            'join_url' => 'https://provider.example/join',
            'host_url' => 'https://provider.example/start',
            'passcode' => '123456',
            'status' => 'scheduled',
        ];
    }

    public function hostUrl(VideoMeeting $meeting): string
    {
        return (string) $meeting->host_url;
    }

    public function embeddedMeetingConfig(VideoMeeting $meeting, bool $host, string $userName, ?string $userEmail = null): array
    {
        return [];
    }
}
