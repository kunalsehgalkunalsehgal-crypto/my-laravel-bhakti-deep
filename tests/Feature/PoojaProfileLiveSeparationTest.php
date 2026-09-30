<?php

namespace Tests\Feature;

use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoojaProfileLiveSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_separate_live_and_digital_pooja_details_and_actions(): void
    {
        $user = User::factory()->create();
        $onlinePandit = $this->pandit('Online Pandit', 'online-profile-pandit@example.test');
        $offlinePandit = $this->pandit('Offline Pandit', 'offline-profile-pandit@example.test');

        $online = $this->poojaSession($user, [
            'pooja_type' => 'live',
            'pooja_type_title' => 'Live Pooja',
            'booking_mode' => 'online',
            'pandit_id' => $onlinePandit->id,
            'booking_date' => '2030-01-10',
            'slot' => '7:00 AM - 8:00 AM',
            'admin_note' => json_encode(['pooja_name' => 'Online Lakshmi Pooja']),
        ]);

        $offline = $this->poojaSession($user, [
            'pooja_type' => 'live',
            'pooja_type_title' => 'Live Pooja',
            'booking_mode' => 'offline',
            'pandit_id' => $offlinePandit->id,
            'booking_date' => '2030-01-11',
            'slot' => '9:00 AM - 10:00 AM',
            'state' => 'Punjab',
            'city' => 'Lalru',
            'admin_note' => json_encode(['pooja_name' => 'Offline Shiv Pooja']),
        ]);

        $digital = $this->poojaSession($user, [
            'pooja_type' => 'digital',
            'pooja_type_title' => 'Digital Ganesh Pooja',
            'status' => 'active',
            'digital_video_path' => 'poojas/videos/ganesh.mp4',
            'admin_note' => json_encode(['pooja_name' => 'Digital Ganesh Pooja']),
        ]);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Live Pooja')
            ->assertSee('Online Pandit')
            ->assertSee('Offline Pandit')
            ->assertSee('Online')
            ->assertSee('Offline')
            ->assertSee('10 Jan 2030')
            ->assertSee('7:00 AM - 8:00 AM')
            ->assertSee('Digital Pooja')
            ->assertSee('Digital Ganesh Pooja')
            ->assertSee('Watch Pooja')
            ->assertSee('View / Join Live Session')
            ->assertSee(route('live.session', ['type' => 'pooja', 'id' => $online->id]), false)
            ->assertSee(route('live.session', ['type' => 'pooja', 'id' => $offline->id]), false)
            ->assertSee(route('pooja.digital.show', $digital), false)
            ->assertDontSee(route('live.session', ['type' => 'pooja', 'id' => $digital->id]), false);
    }

    public function test_live_session_lists_only_include_live_poojas(): void
    {
        $user = User::factory()->create();

        $this->poojaSession($user, [
            'pooja_type' => 'live',
            'pooja_type_title' => 'Live Pooja',
            'booking_mode' => 'offline',
            'status' => 'confirmed',
            'admin_note' => json_encode(['pooja_name' => 'Visible Live Pooja']),
        ]);

        $this->poojaSession($user, [
            'pooja_type' => 'digital',
            'pooja_type_title' => 'Digital Pooja',
            'booking_mode' => 'offline',
            'status' => 'confirmed',
            'admin_note' => json_encode(['pooja_name' => 'Hidden Digital Pooja']),
        ]);

        $this->actingAs($user)
            ->get(route('live.sessions'))
            ->assertOk()
            ->assertSee('1 Booked')
            ->assertDontSee('2 Booked');

        $this->get(route('live.sessions.pooja'))
            ->assertOk()
            ->assertSee('Visible Live Pooja')
            ->assertDontSee('Hidden Digital Pooja');
    }

    public function test_digital_pooja_is_rejected_by_generic_live_and_zoom_routes(): void
    {
        $user = User::factory()->create();
        $digital = $this->poojaSession($user, [
            'pooja_type' => 'digital',
            'status' => 'confirmed',
        ]);

        $this->actingAs($user)
            ->get(route('live.session', ['type' => 'pooja', 'id' => $digital->id]))
            ->assertNotFound();

        $this->get(route('live.session.join', ['type' => 'pooja', 'id' => $digital->id]))
            ->assertNotFound();

        $this->get(route('live.session.start', ['type' => 'pooja', 'id' => $digital->id]))
            ->assertNotFound();

        $this->postJson(route('live.session.sdk', ['type' => 'pooja', 'id' => $digital->id]))
            ->assertNotFound();

        $this->get(route('live.session.client', ['type' => 'pooja', 'id' => $digital->id]))
            ->assertNotFound();
    }

    public function test_legacy_null_pooja_type_still_behaves_as_live(): void
    {
        $user = User::factory()->create();
        $legacy = $this->poojaSession($user, [
            'pooja_type' => null,
            'booking_mode' => 'offline',
            'status' => 'confirmed',
            'admin_note' => json_encode(['pooja_name' => 'Legacy Live Pooja']),
        ]);

        $this->actingAs($user)
            ->get(route('live.sessions.pooja'))
            ->assertOk()
            ->assertSee('Legacy Live Pooja');

        $this->get(route('live.session', ['type' => 'pooja', 'id' => $legacy->id]))
            ->assertOk();
    }

    private function poojaSession(User $user, array $attributes): PoojaSession
    {
        return PoojaSession::create(array_merge([
            'user_id' => $user->id,
            'service_type' => 'pooja',
            'ritual_slug' => 'profile-pooja',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ], $attributes));
    }

    private function pandit(string $name, string $email): Pandit
    {
        return Pandit::create([
            'full_name' => $name,
            'pandit_name' => $name,
            'email' => $email,
            'status' => 'verified',
        ]);
    }
}
