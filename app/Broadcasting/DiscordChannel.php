<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;

class DiscordChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toDiscord')) {
            return;
        }

        $message = $notification->toDiscord($notifiable);

        // Ensure the Discord webhook URL is available in the .env file
        $webhookUrl = config('discord.webhook_url');

        if (!$webhookUrl) {
            throw new \Exception('Discord Webhook URL is not set in the environment');
        }

        // Make the HTTP request to Discord using Guzzle HTTP Client
        $client = new \GuzzleHttp\Client();
        $response = $client->post($webhookUrl, [
            'json' => [
                'content' => $message->content,
                'username' => config('discord.username'),
                'embeds' => $message->embeds,
            ],
        ]);

        if ($response->getStatusCode() !== 204) {
            throw new \Exception('Failed to send message to Discord. Status code: ' . $response->getStatusCode());
        }
    }
}
