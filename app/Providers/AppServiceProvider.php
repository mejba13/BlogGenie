<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use App\Broadcasting\DiscordChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Any other registration code you might have
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Register custom notification driver for Discord
        $this->app->make(ChannelManager::class)->extend('discord', function () {
            return new DiscordChannel();
        });
    }
}
