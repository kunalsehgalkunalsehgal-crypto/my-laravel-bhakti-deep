<?php

namespace Tests\Feature;

use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditAvailabilitySetting;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LivePoojaBookingFlowTest extends TestCase
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
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_live_pooja',
                'amount' => 150100,
                'currency' => 'INR',
                'status' => 'created',
            ]),
        ]);

        $this->actingAs(User::factory()->create());
    }

    public function test_online_live_pooja_uses_online_eligible_pandit(): void
    {
        $session = $this->bookLivePooja('online');

        $this->assertSame('live', $session->pooja_type);
        $this->assertSame('online', $session->booking_mode);
        $this->assertNull($session->state);
        $this->assertNull($session->city);
    }

    public function test_offline_live_pooja_uses_local_offline_eligible_pandit(): void
    {
        $session = $this->bookLivePooja('offline');

        $this->assertSame('live', $session->pooja_type);
        $this->assertSame('offline', $session->booking_mode);
        $this->assertSame('Punjab', $session->state);
        $this->assertSame('Lalru', $session->city);
    }

    private function bookLivePooja(string $bookingMode): PoojaSession
    {
        $date = Carbon::tomorrow('Asia/Kolkata');
        $pooja = Pooja::create([
            'name' => 'Live Flow Pooja',
            'slug' => 'live-flow-pooja',
            'base_price' => 1001,
            'live_pooja_enabled' => true,
            'live_pooja_title' => 'Live Pooja',
            'live_pooja_description' => 'Live personalized Pooja.',
            'live_pooja_price' => 1501,
            'digital_pooja_enabled' => false,
            'mode' => 'Live + Replay',
            'status' => 'active',
        ]);

        $pandit = Pandit::create([
            'full_name' => 'Live Pooja Pandit',
            'pandit_name' => 'Live Pooja Pandit',
            'email' => $bookingMode.'-pooja-pandit@example.test',
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
            'online_pooja' => $bookingMode === 'online',
        ]);

        PanditAvailabilitySetting::create([
            'pandit_id' => $pandit->id,
            'accept_new_bookings' => true,
            'offline_pooja' => $bookingMode === 'offline',
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

        $location = $bookingMode === 'offline'
            ? ['state' => 'Punjab', 'city' => 'Lalru']
            : [];

        $this->get(route('pooja.pandits', array_merge([
            'slug' => $pooja->slug,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Live Pooja',
            'pooja_type' => 'live',
            'booking_mode' => $bookingMode,
        ], $location)))
            ->assertOk()
            ->assertSee('Live Pooja Pandit')
            ->assertSee('name="pooja_type" value="live"', false);

        $this->post(route('pooja.pandits.select', [$pooja->slug, $pandit]), array_merge([
            'pandit_service_id' => $service->id,
            'date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Live Pooja',
            'pooja_type' => 'live',
            'booking_mode' => $bookingMode,
        ], $location))
            ->assertRedirect(route('pooja.review', $pooja->slug))
            ->assertSessionHas('pooja_booking.pooja_type', 'live')
            ->assertSessionHas('pooja_booking.booking_mode', $bookingMode);

        $this->postJson(route('pooja.store'), [
            'pooja_slug' => $pooja->slug,
            'pooja_type' => 'live',
            'package_name' => 'Live Pooja',
            'full_name' => 'Live Pooja Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
            'donation_amount' => 0,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ])->assertOk()->assertJson(['success' => true]);

        $session = PoojaSession::firstOrFail();

        $this->assertSame('Live Pooja', $session->pooja_type_title);
        $this->assertSame('1501.00', $session->pooja_type_price);
        $this->assertSame($pandit->id, $session->pandit_id);
        $this->assertDatabaseHas('payment_attempts', [
            'payable_type' => PoojaSession::class,
            'payable_id' => $session->id,
            'amount' => 1501,
        ]);

        return $session;
    }
}
