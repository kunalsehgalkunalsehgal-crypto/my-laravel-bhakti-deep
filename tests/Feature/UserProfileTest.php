<?php

namespace Tests\Feature;

use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use App\Models\Admin\DiyaSession;
use App\Models\Admin\HawanSession;
use App\Models\Admin\NotificationLog;
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

    public function test_user_notifications_page_shows_all_categories_and_marks_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $booking = NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'booking_confirmed',
            'subject' => 'Booking confirmed',
            'message' => 'Your booking has been confirmed.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'payment_verified',
            'subject' => 'Payment update',
            'message' => 'Payment received for your booking.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
            'read_at' => now(),
        ]);
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'booking_refund',
            'subject' => 'Refund update',
            'message' => 'Refund Processed ₹1,100',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'report_under_review_1',
            'subject' => 'Report update',
            'message' => 'Your report #1 is under review.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'my_bookings',
            'message_type' => 'live_session_ready',
            'subject' => 'Session update',
            'message' => 'Your live session is ready.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::create([
            'user_id' => $otherUser->id,
            'channel' => 'my_bookings',
            'message_type' => 'booking_confirmed',
            'subject' => 'Other booking',
            'message' => 'Other user notification.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('View all notifications')
            ->assertSee('Your booking has been confirmed.')
            ->assertDontSee('Other user notification.');

        $this->actingAs($user)
            ->get(route('user.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('4 unread updates')
            ->assertSee('Booking')
            ->assertSee('Payment')
            ->assertSee('Refund')
            ->assertSee('Report')
            ->assertSee('Session')
            ->assertSee('Refund Processed ₹1,100')
            ->assertDontSee('Other user notification.');

        $this->actingAs($user)
            ->post(route('user.notifications.read', ['notification' => $booking]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Notification marked as read.');

        $this->assertNotNull($booking->fresh()->read_at);

        $this->actingAs($user)
            ->post(route('user.notifications.read-all'))
            ->assertRedirect()
            ->assertSessionHas('success', 'All notifications marked as read.');

        $this->assertSame(0, NotificationLog::where('user_id', $user->id)->whereNull('read_at')->count());
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
