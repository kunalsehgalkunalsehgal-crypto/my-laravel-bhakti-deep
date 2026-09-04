<?php

namespace Tests\Feature;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\Admin\SankalpForm;
use App\Models\LiveSessionInvite;
use App\Models\Pandit\Pandit;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Models\VideoMeetingAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanditLiveSessionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_pandit_can_open_secure_live_page_with_presence(): void
    {
        [$pandit, $booking] = $this->bookingFixture(HawanSession::class, 'hawan');

        VideoMeetingAttendance::create([
            'video_meeting_id' => $booking->videoMeeting->id,
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_USER,
            'participant_id' => $booking->user_id,
            'zoom_participant_id' => 'zoom-user-1',
            'provider' => 'zoom',
            'event_type' => VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED,
            'joined_at' => now()->setTime(7, 5),
            'provider_event_id' => 'main-devotee-joined',
        ]);

        LiveSessionInvite::create([
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
            'name' => 'Maa',
            'relation' => 'Mother',
            'token_hash' => 'family-present',
            'joined_at' => now()->setTime(7, 8),
            'last_seen_at' => now(),
        ]);

        LiveSessionInvite::create([
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
            'name' => 'Brother',
            'relation' => 'Brother',
            'token_hash' => 'family-left',
            'joined_at' => now()->setTime(7, 10),
            'left_at' => now()->setTime(7, 30),
            'last_seen_at' => now(),
        ]);

        LiveSessionInvite::create([
            'session_type' => HawanSession::class,
            'session_id' => $booking->id,
            'name' => 'Sister',
            'relation' => 'Sister',
            'token_hash' => 'family-not-joined',
        ]);

        $response = $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.live-sessions.show', ['type' => 'hawan', 'id' => $booking->id]));

        $response->assertOk()
            ->assertSee('Pandit Live Room')
            ->assertSee('Mahamrityunjaya Hawan')
            ->assertSee('Sankalp Details')
            ->assertSee('Health and peace')
            ->assertSee('Who Is Present')
            ->assertSee('Main Devotee')
            ->assertSee('Maa')
            ->assertSee('Brother')
            ->assertSee('Sister')
            ->assertSee('Present')
            ->assertSee('Left')
            ->assertSee('Not Joined')
            ->assertSee(route('live.session.sdk', ['type' => 'hawan', 'id' => $booking->id, 'mode' => 'host']), false)
            ->assertSee(route('live.session.start', ['type' => 'hawan', 'id' => $booking->id]), false)
            ->assertDontSee('Report an Issue')
            ->assertDontSee('Family Invite')
            ->assertDontSee('Donate')
            ->assertDontSee('Dakshina')
            ->assertDontSee('Total Paid');
    }

    public function test_wrong_pandit_gets_403_for_live_page(): void
    {
        [, $booking] = $this->bookingFixture(PoojaSession::class, 'pooja');
        $wrongPandit = Pandit::create([
            'full_name' => 'Wrong Pandit',
            'pandit_name' => 'Wrong Pandit',
            'email' => 'wrong-pandit@example.test',
            'status' => 'verified',
        ]);

        $this->actingAs($wrongPandit, 'pandit')
            ->get(route('pandit.live-sessions.show', ['type' => 'pooja', 'id' => $booking->id]))
            ->assertForbidden();
    }

    public function test_pandit_live_index_uses_pandit_layout_not_user_live_listing(): void
    {
        [$pandit, $booking] = $this->bookingFixture(PoojaSession::class, 'pooja');

        $response = $this->actingAs($pandit, 'pandit')
            ->get(route('pandit.live-sessions.index'));

        $response->assertOk()
            ->assertSee('Secure Live Rooms')
            ->assertSee('Satyanarayan Pooja')
            ->assertSee(route('pandit.live-sessions.show', ['type' => 'pooja', 'id' => $booking->id]), false)
            ->assertDontSee('Attend free live aarti');
    }

    private function bookingFixture(string $model, string $type): array
    {
        $pandit = Pandit::create([
            'full_name' => 'Assigned Pandit',
            'pandit_name' => 'Assigned Pandit',
            'email' => 'assigned-pandit@example.test',
            'status' => 'verified',
        ]);
        $user = User::factory()->create(['name' => 'Rohan Sen']);
        $sankalp = SankalpForm::create([
            'user_id' => $user->id,
            'full_name' => 'Rohan Sen',
            'mobile' => '9000000000',
            'gotra' => 'Kashyap',
            'family_names' => 'Maa, Brother, Sister',
            'purpose' => 'Health and peace',
        ]);
        $booking = $model::create([
            'user_id' => $user->id,
            'sankalp_form_id' => $sankalp->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'admin_note' => json_encode([
                $type.'_name' => $type === 'hawan' ? 'Mahamrityunjaya Hawan' : 'Satyanarayan Pooja',
            ]),
        ]);

        VideoMeeting::create([
            'provider' => 'zoom',
            'external_meeting_id' => '123456789',
            'join_url' => 'https://zoom.example/join',
            'host_url' => 'https://zoom.example/start',
            'passcode' => '123456',
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
            'duration_minutes' => 60,
            'pandit_id' => $pandit->id,
            'session_type' => $model,
            'session_id' => $booking->id,
        ]);

        return [$pandit, $booking->fresh(['videoMeeting'])];
    }
}
