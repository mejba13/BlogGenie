<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PostTitle;
use App\Jobs\GeneratePostJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GeneratePostsFromTitlesCommand extends Command
{
    protected $signature = 'posts:generate';
    protected $description = 'Generate posts from the post_titles table based on publish_date';

    public function handle()
    {
        $today = now()->format('Y-m-d');

        try {
            // Fetch post titles scheduled for today
            $postTitles = PostTitle::where('publish_date', $today)->get();

            if ($postTitles->isEmpty()) {
                $this->info('No posts to generate for today.');
                return;
            }

            // Dynamically fetch the user (admin or some specific role)
            $user = User::where('role', 'admin')->first(); // Assume there's a 'role' field in your User model
            if (!$user) {
                $this->error('No admin user found!');
                return;
            }

            foreach ($postTitles as $postTitle) {
                // Dispatch the job with both PostTitle and the dynamic User
                GeneratePostJob::dispatch($postTitle, $user);

                // Log the job dispatch
                $this->info('Post generation queued for: ' . $postTitle->title);
                Log::info('Post generation queued for: ' . $postTitle->title);
            }

            // Clear the cached posts after all jobs have been queued
            Cache::forget('posts.all');
            $this->info('Cache cleared for all posts.');

        } catch (\Exception $e) {
            Log::error('Error generating posts from titles: ' . $e->getMessage());
            $this->error('An error occurred while generating posts.');
        }
    }
}
