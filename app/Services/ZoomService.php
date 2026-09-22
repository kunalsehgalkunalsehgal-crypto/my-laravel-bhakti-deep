<?php

namespace App\Services;

use App\Contracts\VideoMeetingProvider;
use App\Models\Pandit\Pandit;
use App\Models\PanditZoomConnection;
use App\Models\VideoMeeting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZoomService implements VideoMeetingProvider
{
    private const TIMEZONE = 'Asia/Kolkata';

    public function providerName(): string
    {
        return 'zoom';
    }

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array
    {
        $connection = $this->connectionFor($pandit);
        $accessToken = $this->accessToken($connection);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic' => $topic,
                'type' => 2,
                'start_time' => $this->formatStartTime($startTime),
                'duration' => $duration,
                'timezone' => self::TIMEZONE,
                'settings' => [
                    'waiting_room' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to create Zoom meeting.');
        }

        $meeting = $response->json();

        return [
            'provider' => 'zoom',
            'external_meeting_id' => (string) ($meeting['id'] ?? ''),
            'join_url' => (string) ($meeting['join_url'] ?? ''),
            'host_url' => (string) ($meeting['start_url'] ?? ''),
            'passcode' => (string) ($meeting['password'] ?? ''),
            'status' => 'scheduled',
        ];
    }

    public function hostUrl(VideoMeeting $meeting): string
    {
        if ($meeting->provider !== $this->providerName()) {
            throw new RuntimeException('Video meeting provider mismatch.');
        }

        if (!$meeting->external_meeting_id || !$meeting->pandit) {
            throw new RuntimeException('Video meeting cannot be started.');
        }

        $connection = $this->connectionFor($meeting->pandit);
        $accessToken = $this->accessToken($connection);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://api.zoom.us/v2/meetings/'.$meeting->external_meeting_id);

        if ($response->failed()) {
            throw new RuntimeException('Unable to get Zoom meeting start URL.');
        }

        $startUrl = (string) ($response->json('start_url') ?? '');

        if ($startUrl === '') {
            throw new RuntimeException('Zoom meeting start URL is unavailable.');
        }

        return $startUrl;
    }

    public function embeddedMeetingConfig(VideoMeeting $meeting, bool $host, string $userName, ?string $userEmail = null): array
    {
        if ($meeting->provider !== $this->providerName()) {
            throw new RuntimeException('Video meeting provider mismatch.');
        }

        if (!$meeting->external_meeting_id) {
            throw new RuntimeException('Video meeting cannot be opened.');
        }

        $meetingNumber = preg_replace('/\D+/', '', $meeting->external_meeting_id);
        $role = $host ? 1 : 0;
        $sdkClientId = config('services.zoom.meeting_sdk_client_id');

        if (!$sdkClientId) {
            throw new RuntimeException('Zoom Meeting SDK client ID is not configured.');
        }

        // return [
        //     'provider' => 'zoom',
        //     'sdkKey' => $sdkClientId,
        //     'meetingNumber' => $meetingNumber,
        //     'password' => (string) $meeting->passcode,
        //     'role' => $role,
        //     'signature' => $this->sdkSignature($meetingNumber, $role),
        //     'userName' => $userName,
        //     'userEmail' => $userEmail ?: '',
        //     'zak' => $host ? $this->zakToken($meeting) : null,
        // ];
        return [
    'provider' => 'zoom',
    'meetingNumber' => $meetingNumber,
    'password' => (string) $meeting->passcode,
    'role' => $role,
    'signature' => $this->sdkSignature($meetingNumber, $role),
    'userName' => $userName,
    'userEmail' => $userEmail ?: '',
    'zak' => $host ? $this->zakToken($meeting) : null,
];
    }

    private function sdkSignature(string $meetingNumber, int $role): string
    {
        $sdkClientId = config('services.zoom.meeting_sdk_client_id');
        $sdkClientSecret = config('services.zoom.meeting_sdk_client_secret');

        if (!$sdkClientId || !$sdkClientSecret) {
            throw new RuntimeException('Zoom Meeting SDK credentials are not configured.');
        }

        $issuedAt = time() - 30;
        $expiresAt = $issuedAt + 7200;
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'appKey' => $sdkClientId,
            'mn' => $meetingNumber,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'tokenExp' => $expiresAt,
        ]));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, $sdkClientSecret, true));

        return $header.'.'.$payload.'.'.$signature;
    }

    private function zakToken(VideoMeeting $meeting): string
    {
        if (!$meeting->pandit) {
            throw new RuntimeException('Video meeting host is unavailable.');
        }

        $connection = $this->connectionFor($meeting->pandit);
        $accessToken = $this->accessToken($connection);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://api.zoom.us/v2/users/me/zak');

        if ($response->failed()) {
            $userId = rawurlencode($connection->zoom_user_id ?: 'me');
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get("https://api.zoom.us/v2/users/{$userId}/token", [
                    'type' => 'zak',
                ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('Unable to get Zoom host authorization.');
        }

        $zak = (string) ($response->json('token') ?? '');

        if ($zak === '') {
            throw new RuntimeException('Zoom host authorization is unavailable.');
        }

        return $zak;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function connectionFor(Pandit $pandit): PanditZoomConnection
    {
        $connection = PanditZoomConnection::query()
            ->where('pandit_id', $pandit->id)
            ->first();

        if (!$connection) {
            throw new RuntimeException('Pandit video provider account is not connected.');
        }

        return $connection;
    }

    private function accessToken(PanditZoomConnection $connection): string
    {
        if (!$connection->token_expires_at || $connection->token_expires_at->isPast()) {
            $this->refreshAccessToken($connection);
        }

        return $connection->access_token;
    }

    private function refreshAccessToken(PanditZoomConnection $connection): void
    {
        $response = Http::asForm()
            ->withBasicAuth(
                config('services.zoom.client_id'),
                config('services.zoom.client_secret')
            )
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to refresh Zoom access token.');
        }

        $tokens = $response->json();

        $connection->forceFill([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? $connection->refresh_token,
            'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
        ])->save();

        $connection->refresh();
    }

    private function formatStartTime(CarbonInterface|string $startTime): string
    {
        return Carbon::parse($startTime)
            ->setTimezone(self::TIMEZONE)
            ->format('Y-m-d\TH:i:s');
    }
}
