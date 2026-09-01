<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Admin\SankalpForm;
use App\Models\Pandit\Pandit;
use App\Models\User;
use App\Models\VideoMeeting;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanditDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_only_assigned_bookings(): void
    {
        [$pandit, $otherPandit] = $this->pandits();
        $user = User::factory()->create(['name' => 'Aarav Sharma']);

        HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Own Hawan', 'dakshina' => 1500, 'total_amount' => 1500]),
        ]);

        PoojaSession::create([
            'user_id' => $user->id,
            'pandit_id' => $otherPandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '8:00 AM - 9:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['pooja_name' => 'Other Pooja', 'total_amount' => 2100]),
        ]);

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.dashboard'))
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertSee('Own Hawan')
            ->assertSee('Aarav Sharma')
            ->assertSee('Rs 1,500')
            ->assertSee('Payment Status')
            ->assertDontSee('Other Pooja');
    }

    public function test_dashboard_shows_latest_five_assigned_hawan_and_pooja_bookings(): void
    {
        [$pandit, $otherPandit] = $this->pandits();
        $user = User::factory()->create(['name' => 'Meera Joshi']);

        $oldBooking = HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDays(7)->toDateString(),
            'slot' => '6:00 AM - 7:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Old Assigned Hawan']),
        ]);
        $this->setCreatedAt($oldBooking, now()->subDays(20));

        foreach (range(1, 5) as $index) {
            $booking = PoojaSession::create([
                'user_id' => $user->id,
                'pandit_id' => $pandit->id,
                'booking_date' => now()->addDays($index)->toDateString(),
                'slot' => '8:00 AM - 9:00 AM',
                'status' => 'scheduled',
                'payment_status' => 'paid',
                'admin_note' => json_encode(['pooja_name' => 'Latest Pooja '.$index]),
            ]);
            $this->setCreatedAt($booking, now()->subDays(10 - $index));
        }

        $otherBooking = HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $otherPandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '9:00 AM - 10:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['hawan_name' => 'Other Pandit Hawan']),
        ]);
        $this->setCreatedAt($otherBooking, now());

        $response = $this->actingAs($pandit, 'pandit')->get(route('pandit.dashboard'));

        $response->assertOk()
            ->assertSee('Latest Assigned Bookings')
            ->assertSee('Latest Pooja 1')
            ->assertSee('Latest Pooja 5')
            ->assertDontSee('Old Assigned Hawan')
            ->assertDontSee('Other Pandit Hawan');
    }

    public function test_bookings_page_shows_all_assigned_bookings(): void
    {
        [$pandit, $otherPandit] = $this->pandits();
        $user = User::factory()->create(['name' => 'Devansh Rao']);

        foreach (range(1, 6) as $index) {
            $booking = $index % 2 === 0
                ? HawanSession::create([
                    'user_id' => $user->id,
                    'pandit_id' => $pandit->id,
                    'booking_date' => now()->addDays($index)->toDateString(),
                    'slot' => '7:00 AM - 8:00 AM',
                    'status' => 'scheduled',
                    'payment_status' => 'paid',
                    'admin_note' => json_encode(['hawan_name' => 'Assigned Hawan '.$index]),
                ])
                : PoojaSession::create([
                    'user_id' => $user->id,
                    'pandit_id' => $pandit->id,
                    'booking_date' => now()->addDays($index)->toDateString(),
                    'slot' => '8:00 AM - 9:00 AM',
                    'status' => 'scheduled',
                    'payment_status' => 'paid',
                    'admin_note' => json_encode(['pooja_name' => 'Assigned Pooja '.$index]),
                ]);

            $this->setCreatedAt($booking, now()->subDays(10 - $index));
        }

        PoojaSession::create([
            'user_id' => $user->id,
            'pandit_id' => $otherPandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '9:00 AM - 10:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['pooja_name' => 'Hidden Pooja']),
        ]);

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.bookings.index'))
            ->assertOk()
            ->assertSee('All Bookings')
            ->assertSee('Assigned Pooja 1')
            ->assertSee('Assigned Hawan 6')
            ->assertSee('View Details')
            ->assertDontSee('Hidden Pooja');
    }

    public function test_booking_details_page_shows_only_assigned_booking_info(): void
    {
        [$pandit, $otherPandit] = $this->pandits();
        $user = User::factory()->create(['name' => 'Isha Kapoor']);
        $sankalp = SankalpForm::create([
            'user_id' => $user->id,
            'full_name' => 'Isha Kapoor',
            'mobile' => '9000000000',
            'gotra' => 'Kashyap',
            'purpose' => 'Griha shanti',
        ]);
        $booking = HawanSession::create([
            'user_id' => $user->id,
            'sankalp_form_id' => $sankalp->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'hawan_type_title' => 'Special Hawan',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode([
                'hawan_name' => 'Mahamrityunjaya Hawan',
                'dakshina' => 501,
                'total_amount' => 4500,
            ]),
        ]);

        $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.bookings.show', ['type' => 'hawan', 'id' => $booking->id]))
            ->assertOk()
            ->assertSee('Mahamrityunjaya Hawan')
            ->assertSee('HAWAN-'.$booking->id)
            ->assertSee('Isha Kapoor')
            ->assertSee('Kashyap')
            ->assertSee('Griha shanti')
            ->assertSee('Special Hawan')
            ->assertSee('Rs 501')
            ->assertSee('Rs 4,500')
            ->assertSee('Paid')
            ->assertSee('Confirmed')
            ->assertSee('Pandit One')
            ->assertDontSee('Unread Notifications');

        $this->actingAs($otherPandit, 'pandit')
            ->get(route('pandit.bookings.show', ['type' => 'hawan', 'id' => $booking->id]))
            ->assertNotFound();
    }

    public function test_pandit_start_session_links_to_internal_live_session(): void
    {
        [$pandit] = $this->pandits();
        $user = User::factory()->create(['name' => 'Rohan Sen']);
        $booking = PoojaSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '8:00 AM - 9:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode(['pooja_name' => 'Satyanarayan Pooja']),
        ]);

        VideoMeeting::create([
            'provider' => 'zoom',
            'external_meeting_id' => '123456789',
            'join_url' => 'https://zoom.example/join',
            'host_url' => 'https://zoom.example/start',
            'status' => 'scheduled',
            'pandit_id' => $pandit->id,
            'session_type' => PoojaSession::class,
            'session_id' => $booking->id,
        ]);

        $response = $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.bookings.index'));

        $response->assertOk()
            ->assertSee(route('live.session', ['type' => 'pooja', 'id' => $booking->id]), false)
            ->assertDontSee(route('live.session.start', ['type' => 'pooja', 'id' => $booking->id]), false)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_only_assigned_pandit_can_accept_booking(): void
    {
        $provider = new PanditDashboardFakeProvider();
        config([
            'video_meetings.default' => 'fake',
            'video_meetings.providers.fake.driver' => PanditDashboardFakeProvider::class,
        ]);
        $this->app->instance(PanditDashboardFakeProvider::class, $provider);

        [$pandit, $otherPandit] = $this->pandits();
        $booking = HawanSession::create([
            'user_id' => User::factory()->create()->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'scheduled',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($otherPandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'hawan', 'id' => $booking->id]))
            ->assertNotFound();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'hawan', 'id' => $booking->id]))
            ->assertRedirect();

        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(1, $provider->calls);
        $this->assertDatabaseHas('video_meetings', [
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
            'pandit_id' => $pandit->id,
        ]);
    }

    public function test_pandit_logout_uses_pandit_guard_and_blocks_back_access(): void
    {
        [$pandit] = $this->pandits();

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.logout'))
            ->assertRedirect(route('login'));

        $this->assertFalse(auth('pandit')->check());

        $this->get(route('pandit.dashboard'))
            ->assertRedirect(route('login'));
    }

    private function pandits(): array
    {
        return [
            Pandit::create([
                'full_name' => 'Pandit One',
                'pandit_name' => 'Pandit One',
                'email' => 'pandit-one@example.test',
                'status' => 'verified',
            ]),
            Pandit::create([
                'full_name' => 'Pandit Two',
                'pandit_name' => 'Pandit Two',
                'email' => 'pandit-two@example.test',
                'status' => 'verified',
            ]),
        ];
    }

    private function setCreatedAt(HawanSession|PoojaSession $session, $createdAt): void
    {
        $session->forceFill(['created_at' => $createdAt])->save();
    }
}

class PanditDashboardFakeProvider implements VideoMeetingProvider
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
            'external_meeting_id' => 'fake-meeting-'.$this->calls,
            'join_url' => 'https://provider.example/join/'.$this->calls,
            'host_url' => 'https://provider.example/start/'.$this->calls,
            'passcode' => '123456',
            'status' => 'scheduled',
        ];
    }

    public function hostUrl($meeting): string
    {
        return 'https://provider.example/start/'.$meeting->external_meeting_id;
    }

    public function embeddedMeetingConfig($meeting, bool $host, string $userName, ?string $userEmail = null): array
    {
        return [];
    }
}
