<?php

namespace Tests\Feature;

use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_login_for_guest_and_profile_for_logged_in_user(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Login with OTP')
            ->assertDontSee('Profile');

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Profile')
            ->assertDontSee('Login with OTP');
    }

    public function test_profile_page_is_for_logged_in_user_and_shows_only_their_bookings(): void
    {
        $user = User::factory()->create(['name' => 'Aarav Sharma']);
        $otherUser = User::factory()->create();
        [$diya, $deity] = $this->diyaFixtures();

        DiyaSession::create([
            'user_id' => $user->id,
            'diya_id' => $diya->id,
            'deity_id' => $deity->id,
            'booking_date' => now()->toDateString(),
            'status' => 'active',
            'payment_status' => 'paid',
        ]);

        PoojaSession::create([
            'user_id' => $user->id,
            'booking_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['pooja_name' => 'Lakshmi Pooja']),
        ]);

        HawanSession::create([
            'user_id' => $user->id,
            'booking_date' => now()->toDateString(),
            'status' => 'scheduled',
            'payment_status' => 'pending',
            'admin_note' => json_encode(['hawan_name' => 'Ganesh Hawan']),
        ]);

        PoojaSession::create([
            'user_id' => $otherUser->id,
            'booking_date' => now()->toDateString(),
            'admin_note' => json_encode(['pooja_name' => 'Other User Pooja']),
        ]);

        $this->get(route('user.profile'))->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Aarav Sharma')
            ->assertSee('Akhand Diya')
            ->assertSee('Lakshmi Pooja')
            ->assertSee('Ganesh Hawan')
            ->assertDontSee('Other User Pooja');
    }

    public function test_user_can_update_profile_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => 'Aarav Sharma',
                'mobile' => '9876543210',
                'dob' => '1992-04-15',
                'gotra' => 'Kashyap',
                'birth_place' => 'Varanasi',
                'address' => 'Dashashwamedh Road, Varanasi',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Aarav Sharma',
            'mobile' => '9876543210',
            'gotra' => 'Kashyap',
            'birth_place' => 'Varanasi',
            'address' => 'Dashashwamedh Road, Varanasi',
        ]);
        $this->assertSame('1992-04-15', $user->fresh()->dob->toDateString());
    }

    public function test_user_can_logout_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    private function diyaFixtures(): array
    {
        $deity = Deity::create([
            'name' => 'Maa Lakshmi',
            'slug' => 'maa-lakshmi',
            'status' => 'active',
        ]);

        $diya = Diya::create([
            'name' => 'Akhand Diya',
            'slug' => 'akhand-diya',
            'seva_amount' => 108,
            'deity_selection_mode' => Diya::MODE_USER_SELECT,
            'status' => 'active',
        ]);

        return [$diya, $deity];
    }
}
