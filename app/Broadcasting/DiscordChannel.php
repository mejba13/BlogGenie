<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class DiscordChannel
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toDiscord')) {
            return;
        }

        $message = $notification->toDiscord($notifiable);

        $webhookUrl = config('services.discord.webhook_url');

        $this->client->post($webhookUrl, [
            'json' => [
                'username' => $message->username ?? config('services.discord.username', 'Laravel Bot'),
                'content' => $message->content,
                'embeds' => $message->embeds ?? [],
            ],
        ]);
    }
}
