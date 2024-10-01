<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;  // We will use HTTP client
use Illuminate\Support\Str;
use App\Models\Post;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function via($notifiable)
    {
        // You can add more channels like 'mail' or 'database' if needed
        return ['discord'];
    }

    /**
     * Send notification to Discord using the webhook URL.
     */
    public function toDiscord($notifiable)
    {
        // Prepare the payload for Discord
        $payload = [
            'username' => config('services.discord.username'),
            'content' => 'A new post has been created: ' . $this->post->title,
            'embeds' => [
                [
                    'title' => $this->post->title,
                    'description' => Str::limit(strip_tags($this->post->content), 200),
                    'url' => route('posts.show', $this->post->id),
                    'footer' => [
                        'text' => 'New post by ' . $this->post->user->name,
                    ],
                    'timestamp' => now(),
                ],
            ],
        ];

        // Send the payload using Discord webhook
        $webhookUrl = config('services.discord.webhook_url');

        try {
            $response = Http::post($webhookUrl, $payload);

            if ($response->failed()) {
                throw new \Exception("Failed to send Discord notification. Response: " . $response->body());
            }

        } catch (\Exception $e) {
            // Log error if Discord webhook fails
            \Log::error('Discord Notification Failed: ' . $e->getMessage());
        }
    }
}
