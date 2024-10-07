<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $errorMessage;

    public function __construct($title, $errorMessage)
    {
        $this->title = $title;
        $this->errorMessage = $errorMessage;
    }

    public function via($notifiable)
    {
        return ['custom_discord'];
    }

    public function toDiscord($notifiable)
    {
        return (object) [
            'content' => 'Failed to generate post: ' . $this->title,
            'embeds' => [
                [
                    'title' => 'Error Occurred',
                    'description' => 'An error occurred while generating the post: "' . $this->title . '"',
                    'fields' => [
                        [
                            'name' => 'Error Details',
                            'value' => $this->errorMessage,
                        ]
                    ],
                    'timestamp' => now(),
                ],
            ],
        ];
    }
}
