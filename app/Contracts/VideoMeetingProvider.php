<?php

namespace App\Contracts;

use App\Models\Pandit\Pandit;
use App\Models\VideoMeeting;
use Carbon\CarbonInterface;

interface VideoMeetingProvider
{
    public function providerName(): string;

    public function createMeeting(Pandit $pandit, string $topic, CarbonInterface|string $startTime, int $duration): array;

    public function hostUrl(VideoMeeting $meeting): string;

    public function embeddedMeetingConfig(VideoMeeting $meeting, bool $host, string $userName, ?string $userEmail = null): array;
}
