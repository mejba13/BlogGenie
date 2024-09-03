<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\WebhookMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use Illuminate\Support\Str;

class NewPostNotification extends Notification
{
    use Queueable;

    protected $post;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['discord'];
    }

    /**
     * Get the Discord webhook representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\WebhookMessage
     */
    public function toDiscord($notifiable)
    {
        return (new WebhookMessage)
            ->content('A new post has been published!')
            ->embeds([
                [
                    'title' => $this->post->title,
                    'description' => Str::limit($this->post->content, 150),
                    'url' => route('posts.show', $this->post->id),
                    'color' => '7506394',
                    'fields' => [
                        [
                            'name' => 'Author',
                            'value' => $this->post->user->name,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Published At',
                            'value' => $this->post->created_at->format('F j, Y'),
                            'inline' => true,
                        ],
                    ],
                ],
            ]);
    }
}
