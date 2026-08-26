<?php

namespace App\Providers;

use App\Contracts\VideoMeetingProvider;
use App\Services\VideoMeetingProviderManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VideoMeetingProvider::class, function ($app) {
            return $app->make(VideoMeetingProviderManager::class)->default();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
