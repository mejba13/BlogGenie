<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DiscordMessage;
use App\Models\Post;
use Illuminate\Support\Str;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;

    /**
     * Create a new notification instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['discord'];
    }

    /**
     * Get the Discord representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\DiscordMessage
     */
    public function toDiscord($notifiable)
    {
        return (new DiscordMessage)
            ->content('A new post has been created on the site!')
            ->username(config('discord.username'))
            ->embed(function ($embed) {
                $embed->title($this->post->title)
                    ->description(Str::limit(strip_tags($this->post->content), 200)) // Limit description to 200 characters
                    ->url(route('posts.show', $this->post->id)) // URL to the post
                    ->footer('New Post by ' . $this->post->user->name)
                    ->timestamp(now());
            });
    }
}
