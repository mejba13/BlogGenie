<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
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
        // Use the custom Discord channel
        return ['custom_discord'];
    }

    public function toDiscord($notifiable)
    {
        return (object) [
            'content' => 'A new post has been created: ' . $this->post->title,
            'embeds' => [
                [
                    'title' => $this->post->title,
                    'description' => Str::limit(strip_tags($this->post->content), 200),
                    'url' => route('admin.posts.show', $this->post->id),
                    'footer' => [
                        'text' => 'New post by ' . $this->post->user->name,
                    ],
                    'timestamp' => now(),
                ],
            ],
        ];
    }
}
