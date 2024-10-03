<?php

use App\Jobs\GeneratePostJob;
use App\Models\PostTitle;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NewPostNotification;
use App\Mail\PostFailedMail;
use Tests\TestCase;

class GeneratePostJobTest extends TestCase
{
    public function test_it_generates_a_post_and_sends_notifications_successfully()
    {
        // Arrange: Fake notifications
        Notification::fake();

        // Arrange: Create necessary models
        $postTitle = PostTitle::factory()->create();
        $user = User::factory()->create();

        // Act: Dispatch the job
        GeneratePostJob::dispatch($postTitle, $user);

        // Assert: Notification was sent
        Notification::assertSentTo(
            [$user],
            NewPostNotification::class
        );
    }

    public function test_it_handles_failure_during_post_generation()
    {
        // Arrange: Fake notifications and mails
        Notification::fake();
        Mail::fake();

        // Arrange: Create necessary models
        $postTitle = PostTitle::factory()->create();
        $user = User::factory()->create();

        // Simulate failure in the OpenAIService
        // Optionally you can mock this service to throw an Exception
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('generatePostData')->andThrow(new \Exception('Some error'));
        });

        // Act: Dispatch the job
        GeneratePostJob::dispatch($postTitle, $user);

        // Assert: Mail was sent with failure email
        Mail::assertSent(PostFailedMail::class, function ($mail) use ($postTitle, $user) {
            return $mail->hasTo($user->email) && $mail->title === $postTitle->title;
        });

        // Assert: Notification was not sent (since the job failed)
        Notification::assertNothingSent();
    }
}
