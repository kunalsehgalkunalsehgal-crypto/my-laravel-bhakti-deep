<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Models\Admin\Hawan;
use App\Models\Admin\HawanSession;
use App\Models\Admin\Pooja;
use App\Models\Admin\PoojaSession;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditAvailabilitySlot;
use App\Models\Pandit\PanditOnlineSetup;
use App\Models\Pandit\PanditService;
use App\Models\PanditZoomConnection;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Services\VideoMeetingService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VideoMeetingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_hawan_and_pooja_meetings_create_only_after_paid_booking_is_confirmed(): void
    {
        $provider = new FakeVideoMeetingProvider();
        $this->app->instance(VideoMeetingProvider::class, $provider);

        [$pandit, $hawan, $pooja, $hawanService, $poojaService] = $this->bookingFixtures();

        $hawanDate = Carbon::tomorrow('Asia/Kolkata');
        $poojaDate = Carbon::tomorrow('Asia/Kolkata')->addDay();

        $this->withSession(['hawan_booking' => $this->hawanBookingSession()])
            ->postJson('/book-hawan', $this->hawanPayload($hawan, $hawanDate))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->withSession(['pooja_booking' => $this->poojaBookingSession()])
            ->postJson('/book-pooja', $this->poojaPayload($pooja, $poojaDate))
            ->assertOk()
            ->assertJson(['success' => true]);

        $hawanSession = HawanSession::firstOrFail();
        $poojaSession = PoojaSession::firstOrFail();

        $this->assertSame('scheduled', $hawanSession->status);
        $this->assertSame('scheduled', $poojaSession->status);
        $this->assertSame('paid', $hawanSession->payment_status);
        $this->assertSame('paid', $poojaSession->payment_status);
        $this->assertSame(0, $provider->calls);
        $this->assertDatabaseCount('video_meetings', 0);

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'hawan', 'id' => $hawanSession->id]))
            ->assertRedirect();
        app(VideoMeetingService::class)->createForSessionIfReady($hawanSession->fresh());

        $this->actingAs($pandit, 'pandit')
            ->post(route('pandit.bookings.accept', ['type' => 'pooja', 'id' => $poojaSession->id]))
            ->assertRedirect();
        app(VideoMeetingService::class)->createForSessionIfReady($poojaSession->fresh());

        $this->assertSame(2, $provider->calls);
        $this->assertDatabaseCount('video_meetings', 2);
        $this->assertDatabaseHas('video_meetings', [
            'provider' => 'fake',
            'session_type' => HawanSession::class,
            'session_id' => $hawanSession->id,
            'pandit_id' => $pandit->id,
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('video_meetings', [
            'provider' => 'fake',
            'session_type' => PoojaSession::class,
            'session_id' => $poojaSession->id,
            'pandit_id' => $pandit->id,
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $this->assertSame($hawanService->id, $hawanSession->pandit_service_id);
        $this->assertSame($poojaService->id, $poojaSession->pandit_service_id);
    }

    public function test_start_and_join_routes_authorize_the_right_people(): void
    {
        $this->app->instance(FakeVideoMeetingProvider::class, new FakeVideoMeetingProvider());
        config(['video_meetings.providers.fake.driver' => FakeVideoMeetingProvider::class]);

        $user = $this->user('owner@example.test');
        $wrongUser = $this->user('wrong@example.test');
        $pandit = Pandit::create([
            'full_name' => 'Pandit Owner',
            'pandit_name' => 'Pandit Owner',
            'email' => 'pandit-owner@example.test',
            'status' => 'verified',
        ]);
        $wrongPandit = Pandit::create([
            'full_name' => 'Other Pandit',
            'pandit_name' => 'Other Pandit',
            'email' => 'other-pandit@example.test',
            'status' => 'verified',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'service_type' => 'hawan',
            'pandit_id' => $pandit->id,
            'booking_date' => now()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'slot_start_time' => '07:00:00',
            'slot_end_time' => '08:00:00',
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

        $this->actingAs($user)
            ->get(route('live.session.join', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect('https://provider.example/join/meeting-1');

        $this->actingAs($wrongUser)
            ->get(route('live.session.join', ['type' => 'hawan', 'id' => $session->id]))
            ->assertForbidden();

        $this->actingAs($pandit, 'pandit')
            ->get(route('live.session.start', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect('https://provider.example/start/meeting-1');

        $this->actingAs($user)
            ->post(route('live.session.sdk', ['type' => 'hawan', 'id' => $session->id]))
            ->assertOk()
            ->assertJson([
                'provider' => 'fake',
                'role' => 0,
            ]);

        $this->actingAs($pandit, 'pandit')
            ->post(route('live.session.sdk', ['type' => 'hawan', 'id' => $session->id, 'mode' => 'host']))
            ->assertOk()
            ->assertJson([
                'provider' => 'fake',
                'role' => 1,
            ]);

        $this->actingAs($wrongPandit, 'pandit')
            ->get(route('live.session.start', ['type' => 'hawan', 'id' => $session->id]))
            ->assertForbidden();

        $this->actingAs($wrongUser)
            ->post(route('live.session.sdk', ['type' => 'hawan', 'id' => $session->id]))
            ->assertForbidden();
    }

    public function test_zoom_service_refreshes_expired_token_before_creating_meeting(): void
    {
        config([
            'services.zoom.client_id' => 'test-client-id',
            'services.zoom.client_secret' => 'test-client-secret',
        ]);

        $pandit = Pandit::create([
            'full_name' => 'Token Pandit',
            'pandit_name' => 'Token Pandit',
            'email' => 'token-pandit@example.test',
            'status' => 'verified',
        ]);
        $connection = PanditZoomConnection::create([
            'pandit_id' => $pandit->id,
            'zoom_user_id' => 'zoom-user-1',
            'zoom_email' => 'zoom@example.test',
            'access_token' => 'expired-access-token',
            'refresh_token' => 'old-refresh-token',
            'token_expires_at' => now()->subMinute(),
            'connected_at' => now()->subDay(),
        ]);

        Http::fake([
            'https://zoom.us/oauth/token' => Http::response([
                'access_token' => 'fresh-access-token',
                'refresh_token' => 'fresh-refresh-token',
                'expires_in' => 7200,
            ]),
            'https://api.zoom.us/v2/users/me/meetings' => Http::response([
                'id' => 987654321,
                'join_url' => 'https://provider.example/join/987654321',
                'start_url' => 'https://provider.example/start/987654321',
                'password' => '654321',
            ], 201),
        ]);

        $meeting = app(ZoomService::class)->createMeeting(
            $pandit,
            'Test Meeting',
            Carbon::parse('2026-08-30 07:00:00', 'Asia/Kolkata'),
            60
        );

        $connection->refresh();

        $this->assertSame('fresh-access-token', $connection->access_token);
        $this->assertSame('fresh-refresh-token', $connection->refresh_token);
        $this->assertTrue($connection->token_expires_at->isFuture());
        $this->assertSame([
            'provider' => 'zoom',
            'external_meeting_id' => '987654321',
            'join_url' => 'https://provider.example/join/987654321',
            'host_url' => 'https://provider.example/start/987654321',
            'passcode' => '654321',
            'status' => 'scheduled',
        ], $meeting);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://zoom.us/oauth/token');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.zoom.us/v2/users/me/meetings'
            && $request->hasHeader('Authorization', 'Bearer fresh-access-token')
            && $request['timezone'] === 'Asia/Kolkata'
            && $request['settings']['waiting_room'] === true
            && $request['settings']['join_before_host'] === false
            && $request['settings']['mute_upon_entry'] === true);
    }

    public function test_zoom_service_generates_meeting_sdk_payload_with_host_zak(): void
    {
        config([
            'services.zoom.client_id' => 'oauth-client-id',
            'services.zoom.client_secret' => 'oauth-client-secret',
            'services.zoom.meeting_sdk_client_id' => 'sdk-client-id',
            'services.zoom.meeting_sdk_client_secret' => 'sdk-client-secret',
        ]);

        $pandit = Pandit::create([
            'full_name' => 'SDK Pandit',
            'pandit_name' => 'SDK Pandit',
            'email' => 'sdk-pandit@example.test',
            'status' => 'verified',
        ]);
        PanditZoomConnection::create([
            'pandit_id' => $pandit->id,
            'zoom_user_id' => 'zoom-user-2',
            'zoom_email' => 'zoom-sdk@example.test',
            'access_token' => 'fresh-access-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now()->subDay(),
        ]);
        $meeting = VideoMeeting::create([
            'provider' => 'zoom',
            'external_meeting_id' => '987654321',
            'join_url' => 'https://zoom.example/join',
            'host_url' => 'https://zoom.example/start',
            'passcode' => '654321',
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
            'duration_minutes' => 60,
            'pandit_id' => $pandit->id,
            'session_type' => HawanSession::class,
            'session_id' => 999,
        ]);

        Http::fake([
            'https://api.zoom.us/v2/users/me/zak' => Http::response([
                'token' => 'fresh-host-zak',
            ]),
        ]);

        $payload = app(ZoomService::class)->embeddedMeetingConfig($meeting, true, 'SDK Pandit', $pandit->email);

        $this->assertSame('zoom', $payload['provider']);
        $this->assertSame('sdk-client-id', $payload['sdkKey']);
        $this->assertSame('987654321', $payload['meetingNumber']);
        $this->assertSame(1, $payload['role']);
        $this->assertSame('fresh-host-zak', $payload['zak']);
        $this->assertArrayNotHasKey('clientSecret', $payload);
        $this->assertStringNotContainsString('sdk-client-secret', json_encode($payload));

        $claims = json_decode(base64_decode(strtr(explode('.', $payload['signature'])[1], '-_', '+/')), true);

        $this->assertSame('sdk-client-id', $claims['appKey']);
        $this->assertSame('987654321', $claims['mn']);
        $this->assertSame(1, $claims['role']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.zoom.us/v2/users/me/zak'
            && $request->hasHeader('Authorization', 'Bearer fresh-access-token'));
    }

    private function bookingFixtures(): array
    {
        $pandit = Pandit::create([
            'full_name' => 'Pandit Test',
            'pandit_name' => 'Pandit Test',
            'email' => 'pandit@example.test',
            'status' => 'verified',
        ]);
        $hawan = Hawan::create([
            'name' => 'Test Hawan',
            'slug' => 'test-hawan',
            'base_price' => 1100,
            'samuhik_hawan_enabled' => true,
            'samuhik_hawan_title' => 'Samuhik Hawan',
            'samuhik_hawan_price' => 1100,
            'duration' => '60 min',
            'mode' => 'Live + Replay',
            'status' => 'active',
        ]);
        $pooja = Pooja::create([
            'name' => 'Test Pooja',
            'slug' => 'test-pooja',
            'base_price' => 1000,
            'duration' => '60 min',
            'mode' => 'Live + Replay',
            'status' => 'active',
        ]);
        $hawanService = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'hawan',
            'service_name' => 'Test Hawan',
            'hawan_id' => $hawan->id,
            'duration_minutes' => 60,
            'status' => 'approved',
        ]);
        $poojaService = PanditService::create([
            'pandit_id' => $pandit->id,
            'service_type' => 'pooja',
            'service_name' => 'Test Pooja',
            'pooja_id' => $pooja->id,
            'duration_minutes' => 60,
            'status' => 'approved',
        ]);

        PanditOnlineSetup::create([
            'pandit_id' => $pandit->id,
            'online_hawan' => true,
            'online_pooja' => true,
            'stable_internet' => true,
        ]);

        foreach ([Carbon::tomorrow('Asia/Kolkata'), Carbon::tomorrow('Asia/Kolkata')->addDay()] as $date) {
            PanditAvailabilitySlot::create([
                'pandit_id' => $pandit->id,
                'day' => $date->format('l'),
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'is_available' => true,
            ]);
        }

        return [$pandit, $hawan, $pooja, $hawanService, $poojaService];
    }

    private function hawanPayload(Hawan $hawan, CarbonInterface $date): array
    {
        return [
            'hawan_slug' => $hawan->slug,
            'hawan_type' => 'samuhik',
            'package_name' => 'Samuhik Hawan',
            'full_name' => 'Hawan Devotee',
            'mobile' => '9999999999',
            'purpose' => 'Peace',
            'donation_amount' => 0,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ];
    }

    private function poojaPayload(Pooja $pooja, CarbonInterface $date): array
    {
        return [
            'pooja_slug' => $pooja->slug,
            'package_name' => 'Standard Pooja',
            'full_name' => 'Pooja Devotee',
            'mobile' => '8888888888',
            'purpose' => 'Prosperity',
            'donation_amount' => 0,
            'booking_date' => $date->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'otp' => '123456',
        ];
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function hawanBookingSession(): array
    {
        $hawan = Hawan::where('slug', 'test-hawan')->first();
        $service = PanditService::where('service_type', 'hawan')->first();

        return [
            'service_type' => 'hawan',
            'service_id' => $hawan?->id,
            'service_slug' => 'test-hawan',
            'pandit_service_id' => $service?->id,
            'pandit_id' => $service?->pandit_id,
            'date' => Carbon::tomorrow('Asia/Kolkata')->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Samuhik Hawan',
            'hawan_type' => 'samuhik',
        ];
    }

    private function poojaBookingSession(): array
    {
        $pooja = Pooja::where('slug', 'test-pooja')->first();
        $service = PanditService::where('service_type', 'pooja')->first();

        return [
            'service_type' => 'pooja',
            'service_id' => $pooja?->id,
            'service_slug' => 'test-pooja',
            'pandit_service_id' => $service?->id,
            'pandit_id' => $service?->pandit_id,
            'date' => Carbon::tomorrow('Asia/Kolkata')->addDay()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'mode' => 'Standard Pooja',
        ];
    }
}

class FakeVideoMeetingProvider implements VideoMeetingProvider
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
            'zak' => $host ? 'fake-zak' : null,
        ];
    }
}
