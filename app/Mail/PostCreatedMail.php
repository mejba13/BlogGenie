<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PostCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function build()
    {
        return $this->subject('New Post Created Successfully')
            ->view('emails.post_created')
            ->with([
                'title' => $this->post->title,
                'content' => $this->post->content,
                'published_at' => $this->post->published_at,
            ]);
    }
}
