<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class DiscordChannel
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($notifiable, Notification $notification)
    {
        $webhookUrl = config('services.discord.webhook_url');

        $data = $notification->toDiscord($notifiable)->toArray();

        return $this->client->post($webhookUrl, [
            'json' => $data,
        ]);
    }
}
