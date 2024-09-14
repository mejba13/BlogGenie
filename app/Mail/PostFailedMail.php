<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PostFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $error;

    public function __construct($title, $error)
    {
        $this->title = $title;
        $this->error = $error;
    }

    public function build()
    {
        return $this->subject('Post Creation Failed')
            ->view('emails.post_failed')
            ->with([
                'title' => $this->title,
                'error' => $this->error,
            ]);
    }
}
