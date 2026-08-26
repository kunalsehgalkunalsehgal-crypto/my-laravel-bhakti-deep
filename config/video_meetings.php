<?php

use App\Services\ZoomService;

return [
    'default' => env('VIDEO_MEETING_PROVIDER', 'zoom'),

    'providers' => [
        'zoom' => [
            'driver' => ZoomService::class,
            'meeting_sdk_cdn_version' => env('ZOOM_MEETING_SDK_CDN_VERSION', '3.13.2'),
        ],
    ],
];
