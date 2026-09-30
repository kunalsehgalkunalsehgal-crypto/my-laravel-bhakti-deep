<?php

namespace Tests\Feature;

use App\Models\Admin\Audio;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DigitalPoojaBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_pooja_can_be_booked_without_pandit_date_or_slot(): void
    {
        config([
            'services.razorpay.key_id' => 'rzp_test_key',
            'services.razorpay.key_secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_digital_pooja',
                'status' => 'created',
            ]),
        ]);

        $this->actingAs(User::factory()->create());

        $mantra = Audio::create([
            'title' => 'Peace Mantra',
            'slug' => 'peace-mantra',
            'category' => 'mantra',
            'audio_file' => 'audio/peace-mantra.mp3',
            'status' => 'active',
        ]);

        $pooja = Pooja::create([
            'name' => 'Digital Booking Pooja',
            'slug' => 'digital-booking-pooja',
            'base_price' => 100,
            'live_pooja_enabled' => false,
            'digital_pooja_enabled' => true,
            'digital_pooja_title' => 'Recorded Digital Pooja',
            'digital_pooja_description' => 'A recorded Pooja for your Sankalp.',
            'digital_pooja_price' => 751,
            'digital_pooja_video' => 'poojas/videos/digital-pooja.mp4',
            'digital_pooja_audio_id' => $mantra->id,
            'digital_pooja_access_minutes' => 60,
            'status' => 'active',
        ]);

        $this->postJson(route('pooja.store'), [
            'pooja_slug' => $pooja->slug,
            'pooja_type' => 'digital',
            'package_name' => 'Untrusted frontend package',
            'package_amount' => 1,
            'total_amount' => 1,
            'full_name' => 'Digital Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
        ])->assertOk()->assertJson([
            'success' => true,
            'payment_status' => 'pending',
        ]);

        $session = PoojaSession::firstOrFail();

        $this->assertSame('digital', $session->pooja_type);
        $this->assertSame('Recorded Digital Pooja', $session->pooja_type_title);
        $this->assertSame('751.00', $session->pooja_type_price);
        $this->assertSame('poojas/videos/digital-pooja.mp4', $session->digital_video_path);
        $this->assertSame($mantra->id, $session->digital_audio_id);
        $this->assertSame('Peace Mantra', $session->digital_audio_title);
        $this->assertSame('audio/peace-mantra.mp3', $session->digital_audio_path);
        $this->assertSame(60, $session->digital_access_minutes);
        $this->assertSame('pending', $session->payment_status);
        $this->assertNull($session->booking_mode);
        $this->assertNull($session->state);
        $this->assertNull($session->city);
        $this->assertNull($session->pandit_id);
        $this->assertNull($session->pandit_service_id);
        $this->assertNull($session->booking_date);
        $this->assertNull($session->slot);
        $this->assertSame('751.00', $session->latestPaymentAttempt->amount);
        $this->assertDatabaseCount('video_meetings', 0);
    }
}
