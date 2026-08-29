<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Models\Admin\Donation;
use App\Models\Admin\HawanSession;
use App\Models\Admin\PaymentLog;
use App\Models\LiveSessionInvite;
use App\Models\Pandit\Pandit;
use App\Models\User;
use App\Models\VideoMeeting;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LiveSessionFamilyAndDakshinaTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_invite_and_family_can_join_with_token_without_login(): void
    {
        config(['video_meetings.providers.fake.driver' => LiveSessionFakeProvider::class]);
        $this->app->instance(LiveSessionFakeProvider::class, new LiveSessionFakeProvider());

        [$user, $session] = $this->liveSession();

        $inviteResponse = $this->actingAs($user)
            ->postJson(route('live.family.store', ['type' => 'hawan', 'id' => $session->id]), [
                'name' => 'Sita Sharma',
                'relation' => 'Mother',
            ])
            ->assertOk()
            ->assertJsonPath('invite.name', 'Sita Sharma')
            ->assertJsonPath('invite.status', 'Invited');

        $joinUrl = $inviteResponse->json('invite.join_url');
        $token = basename(parse_url($joinUrl, PHP_URL_PATH));
        Auth::guard('web')->logout();

        $this->assertDatabaseHas('live_session_invites', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'name' => 'Sita Sharma',
            'relation' => 'Mother',
        ]);

        $this->get(route('live.family.join', ['token' => $token]))
            ->assertOk()
            ->assertSee('Sita Sharma');

        $this->assertGuest();
        $this->assertNotNull(LiveSessionInvite::first()->joined_at);

        $this->postJson(route('live.family.sdk', ['token' => $token]))
            ->assertOk()
            ->assertJson([
                'provider' => 'fake',
                'role' => 0,
                'userName' => 'Sita Sharma',
            ]);
    }

    public function test_invite_and_dakshina_are_limited_to_booking_owner_and_revoke_blocks_join(): void
    {
        [$user, $session] = $this->liveSession();
        $wrongUser = User::factory()->create();

        $this->actingAs($wrongUser)
            ->postJson(route('live.family.store', ['type' => 'hawan', 'id' => $session->id]), [
                'name' => 'Wrong User',
                'relation' => 'Guest',
            ])
            ->assertForbidden();

        $inviteResponse = $this->actingAs($user)
            ->postJson(route('live.family.store', ['type' => 'hawan', 'id' => $session->id]), [
                'name' => 'Ramesh Sharma',
                'relation' => 'Father',
            ]);

        $invite = LiveSessionInvite::firstOrFail();
        $token = basename(parse_url($inviteResponse->json('invite.join_url'), PHP_URL_PATH));

        $this->actingAs($wrongUser)
            ->postJson(route('live.dakshina.pay', ['type' => 'hawan', 'id' => $session->id]), ['amount' => 251])
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('live.dakshina.pay', ['type' => 'hawan', 'id' => $session->id]), ['amount' => 251])
            ->assertOk()
            ->assertJsonPath('amount', 251);

        $this->assertSame(251.0, (float) Donation::first()->amount);
        $this->assertSame(251.0, (float) PaymentLog::first()->amount);
        $this->assertTrue(PaymentLog::first()->payload['server_verified']);

        $this->actingAs($user)
            ->postJson(route('live.family.revoke', ['type' => 'hawan', 'id' => $session->id, 'invite' => $invite]))
            ->assertOk()
            ->assertJsonPath('status', 'Revoked');

        $this->get(route('live.family.join', ['token' => $token]))->assertForbidden();
    }

    private function liveSession(): array
    {
        $user = User::factory()->create([
            'name' => 'Aarav Sharma',
            'email' => 'aarav@example.test',
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Pandit Test',
            'pandit_name' => 'Pandit Test',
            'email' => 'pandit@example.test',
            'status' => 'verified',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'pandit_id' => $pandit->id,
            'booking_date' => now()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'live_session_token' => 'private-token',
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $session->videoMeeting()->create([
            'provider' => 'fake',
            'external_meeting_id' => 'meeting-1',
            'join_url' => 'https://provider.example/join/meeting-1',
            'host_url' => 'https://provider.example/start/meeting-1',
            'passcode' => '123456',
            'status' => 'scheduled',
            'starts_at' => now(),
            'duration_minutes' => 60,
            'pandit_id' => $pandit->id,
        ]);

        return [$user, $session->fresh('videoMeeting')];
    }
}

class LiveSessionFakeProvider implements VideoMeetingProvider
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array
    {
        return [];
    }

    public function hostUrl(VideoMeeting $meeting): string
    {
        return 'https://provider.example/start/'.$meeting->external_meeting_id;
    }

    public function embeddedMeetingConfig(VideoMeeting $meeting, bool $host, string $userName, ?string $userEmail = null): array
    {
        return [
            'provider' => 'fake',
            'meetingNumber' => $meeting->external_meeting_id,
            'password' => $meeting->passcode,
            'role' => $host ? 1 : 0,
            'signature' => 'fake-signature',
            'userName' => $userName,
            'userEmail' => $userEmail,
            'zak' => null,
        ];
    }
}
