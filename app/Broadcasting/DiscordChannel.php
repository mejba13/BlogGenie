<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DiscordChannel
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function send($notifiable, Notification $notification)
    {
        // Check if the notification has the toDiscord method
        if (!method_exists($notification, 'toDiscord')) {
            Log::error('Notification does not support Discord channel.');
            return;
        }

        // Retrieve the message from the notification
        $message = $notification->toDiscord($notifiable);

        // Get the Discord webhook URL from the configuration
        $webhookUrl = config('services.discord.webhook_url');

        // Ensure that the webhook URL is configured
        if (empty($webhookUrl)) {
            Log::error('Discord webhook URL is not set.');
            return;
        }

        try {
            // Send the request to Discord using the webhook
            $response = $this->client->post($webhookUrl, [
                'json' => [
                    'username' => $message->username ?? config('services.discord.username', 'Laravel Bot'),
                    'content' => $message->content ?? 'No content provided.',
                    'embeds' => $message->embeds ?? [],
                ],
            ]);

            // Log if the request fails
            if ($response->getStatusCode() !== 204) {
                Log::error('Failed to send Discord notification. Response code: ' . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            // Log the error if the request fails
            Log::error('Error sending Discord notification: ' . $e->getMessage());
        }
    }
}
