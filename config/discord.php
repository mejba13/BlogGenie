<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discord Webhook URL
    |--------------------------------------------------------------------------
    |
    | The webhook URL for sending notifications to a Discord channel.
    |
    */

    'webhook_url' => env('DISCORD_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Discord Bot Username
    |--------------------------------------------------------------------------
    |
    | The bot username that will be used when sending notifications to Discord.
    |
    */

    'username' => env('DISCORD_USERNAME', 'Laravel Bot'),

];
