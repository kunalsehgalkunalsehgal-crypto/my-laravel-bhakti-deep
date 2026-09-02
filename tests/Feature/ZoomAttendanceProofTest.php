<?php

namespace Tests\Feature;

use App\Contracts\VideoMeetingProvider;
use App\Models\Admin\HawanSession;
use App\Models\Pandit\Pandit;
use App\Models\PanditZoomConnection;
use App\Models\User;
use App\Models\VideoMeeting;
use App\Models\VideoMeetingAttendance;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoomAttendanceProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_join_and_start_routes_record_attempts_only(): void
    {
        config(['video_meetings.providers.fake.driver' => AttendanceFakeProvider::class]);
        $this->app->instance(AttendanceFakeProvider::class, new AttendanceFakeProvider());

        [$user, $pandit, $session] = $this->sessionWithMeeting('fake', 'meeting-attempt');

        $this->actingAs($user)
            ->get(route('live.session.join', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect('https://provider.example/join/meeting-attempt');

        $this->actingAs($pandit, 'pandit')
            ->get(route('live.session.start', ['type' => 'hawan', 'id' => $session->id]))
            ->assertRedirect('https://provider.example/start/meeting-attempt');

        $this->assertDatabaseHas('video_meeting_attendances', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_USER,
            'participant_id' => $user->id,
            'event_type' => VideoMeetingAttendance::EVENT_JOIN_ATTEMPT,
        ]);

        $this->assertDatabaseHas('video_meeting_attendances', [
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_PANDIT,
            'participant_id' => $pandit->id,
            'event_type' => VideoMeetingAttendance::EVENT_JOIN_ATTEMPT,
        ]);

        $this->assertDatabaseMissing('video_meeting_attendances', [
            'event_type' => VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED,
        ]);
    }

    public function test_signed_zoom_webhooks_record_actual_attendance_and_skip_duplicates(): void
    {
        config(['services.zoom.webhook_secret_token' => 'test-webhook-secret']);
        [$user, $pandit, $session, $meeting] = $this->sessionWithMeeting('zoom', '987654321');

        PanditZoomConnection::create([
            'pandit_id' => $pandit->id,
            'zoom_user_id' => 'zoom-host-user-id',
            'zoom_email' => 'host@example.test',
            'access_token' => 'token',
            'refresh_token' => 'refresh',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);

        $this->signedZoomWebhook([
            'event' => 'meeting.started',
            'event_ts' => 1796202000000,
            'payload' => [
                'object' => [
                    'id' => '987654321',
                    'uuid' => 'meeting-uuid-1',
                    'topic' => 'BhaktiDeep Hawan',
                    'start_time' => '2026-09-02T07:00:00Z',
                ],
            ],
        ])->assertOk();

        $participantJoined = [
            'event' => 'meeting.participant_joined',
            'event_ts' => 1796202060000,
            'payload' => [
                'object' => [
                    'id' => '987654321',
                    'uuid' => 'meeting-uuid-1',
                    'participant' => [
                        'user_id' => 'zoom-host-user-id',
                        'email' => 'host@example.test',
                        'join_time' => '2026-09-02T07:01:00Z',
                    ],
                ],
            ],
        ];

        $this->signedZoomWebhook($participantJoined)->assertOk();
        $this->signedZoomWebhook($participantJoined)->assertOk();

        $this->signedZoomWebhook([
            'event' => 'meeting.participant_left',
            'event_ts' => 1796202960000,
            'payload' => [
                'object' => [
                    'id' => '987654321',
                    'uuid' => 'meeting-uuid-1',
                    'participant' => [
                        'user_id' => 'zoom-guest-user-id',
                        'user_email' => $user->email,
                        'leave_time' => '2026-09-02T07:16:00Z',
                        'duration' => 900,
                    ],
                ],
            ],
        ])->assertOk();

        $this->signedZoomWebhook([
            'event' => 'meeting.ended',
            'event_ts' => 1796205600000,
            'payload' => [
                'object' => [
                    'id' => '987654321',
                    'uuid' => 'meeting-uuid-1',
                    'end_time' => '2026-09-02T08:00:00Z',
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('video_meeting_attendances', 4);
        $this->assertDatabaseHas('video_meeting_attendances', [
            'video_meeting_id' => $meeting->id,
            'session_type' => HawanSession::class,
            'session_id' => $session->id,
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_UNKNOWN,
            'event_type' => VideoMeetingAttendance::EVENT_MEETING_STARTED,
        ]);
        $this->assertDatabaseHas('video_meeting_attendances', [
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_PANDIT,
            'participant_id' => $pandit->id,
            'zoom_participant_id' => 'zoom-host-user-id',
            'event_type' => VideoMeetingAttendance::EVENT_PARTICIPANT_JOINED,
        ]);
        $this->assertDatabaseHas('video_meeting_attendances', [
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_USER,
            'participant_id' => $user->id,
            'zoom_participant_id' => 'zoom-guest-user-id',
            'event_type' => VideoMeetingAttendance::EVENT_PARTICIPANT_LEFT,
            'duration' => 900,
        ]);
        $this->assertDatabaseHas('video_meeting_attendances', [
            'participant_type' => VideoMeetingAttendance::PARTICIPANT_UNKNOWN,
            'event_type' => VideoMeetingAttendance::EVENT_MEETING_ENDED,
        ]);
    }

    public function test_zoom_webhook_rejects_invalid_signature(): void
    {
        config(['services.zoom.webhook_secret_token' => 'test-webhook-secret']);
        $this->sessionWithMeeting('zoom', '987654321');

        $this->call('POST', route('zoom.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => (string) time(),
            'HTTP_X_ZM_SIGNATURE' => 'v0=bad-signature',
        ], json_encode([
            'event' => 'meeting.started',
            'payload' => ['object' => ['id' => '987654321']],
        ]))->assertForbidden();

        $this->assertDatabaseCount('video_meeting_attendances', 0);
    }

    public function test_zoom_url_validation_requires_valid_signature(): void
    {
        config(['services.zoom.webhook_secret_token' => 'test-webhook-secret']);

        $this->signedZoomWebhook([
            'event' => 'endpoint.url_validation',
            'payload' => [
                'plainToken' => 'plain-token',
            ],
        ])
            ->assertOk()
            ->assertJson([
                'plainToken' => 'plain-token',
                'encryptedToken' => hash_hmac('sha256', 'plain-token', 'test-webhook-secret'),
            ]);
    }

    private function sessionWithMeeting(string $provider, string $externalMeetingId): array
    {
        $user = User::factory()->create([
            'name' => 'Attendance User',
            'email' => 'attendance-user@example.test',
        ]);
        $pandit = Pandit::create([
            'full_name' => 'Attendance Pandit',
            'pandit_name' => 'Attendance Pandit',
            'email' => 'attendance-pandit@example.test',
            'status' => 'verified',
        ]);
        $session = HawanSession::create([
            'user_id' => $user->id,
            'service_type' => 'hawan',
            'pandit_id' => $pandit->id,
            'booking_date' => now()->toDateString(),
            'slot' => '7:00 AM - 8:00 AM',
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $meeting = $session->videoMeeting()->create([
            'provider' => $provider,
            'external_meeting_id' => $externalMeetingId,
            'join_url' => 'https://provider.example/join/'.$externalMeetingId,
            'host_url' => 'https://provider.example/start/'.$externalMeetingId,
            'passcode' => '123456',
            'status' => 'scheduled',
            'starts_at' => now(),
            'duration_minutes' => 60,
            'pandit_id' => $pandit->id,
        ]);

        return [$user, $pandit, $session->fresh('videoMeeting'), $meeting];
    }

    private function signedZoomWebhook(array $payload)
    {
        $body = json_encode($payload);
        $timestamp = (string) time();
        $signature = 'v0='.hash_hmac('sha256', 'v0:'.$timestamp.':'.$body, 'test-webhook-secret');

        return $this->call('POST', route('zoom.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_X_ZM_SIGNATURE' => $signature,
        ], $body);
    }
}

class AttendanceFakeProvider implements VideoMeetingProvider
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
            'role' => $host ? 1 : 0,
        ];
    }
}
