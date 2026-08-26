<?php

namespace App\Services;

use App\Contracts\VideoMeetingProvider;
use InvalidArgumentException;

class VideoMeetingProviderManager
{
    public function default(): VideoMeetingProvider
    {
        return $this->for(config('video_meetings.default', 'zoom'));
    }

    public function for(string $provider): VideoMeetingProvider
    {
        $driver = config("video_meetings.providers.{$provider}.driver");

        if (!$driver) {
            throw new InvalidArgumentException("Unsupported video meeting provider [{$provider}].");
        }

        $instance = app($driver);

        if (!$instance instanceof VideoMeetingProvider) {
            throw new InvalidArgumentException("Video meeting provider [{$provider}] is invalid.");
        }

        return $instance;
    }
}
