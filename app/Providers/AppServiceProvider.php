<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use App\Broadcasting\DiscordChannel;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Telescope only in the local environment
        if ($this->app->isLocal()) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }

        // Register Horizon only if enabled and not in production
        if (!$this->app->environment('production') && env('HORIZON_ENABLED', true)) {
            $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
        }
    }

    public function boot()
    {
        // Register custom Discord channel
        $this->app->make(ChannelManager::class)->extend('custom_discord', function () {
            return new DiscordChannel();
        });
    }
}
