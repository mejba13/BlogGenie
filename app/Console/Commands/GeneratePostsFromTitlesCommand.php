<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PostTitle;
use App\Jobs\GeneratePostJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeneratePostsFromTitlesCommand extends Command
{
    protected $signature = 'posts:generate';
    protected $description = 'Generate posts from the post_titles table based on publish_date';

    public function handle()
    {
        $today = now()->format('Y-m-d');

        $postTitles = PostTitle::where('publish_date', $today)->get();

        if ($postTitles->isEmpty()) {
            $this->info('No posts to generate for today.');
            return;
        }

        foreach ($postTitles as $postTitle) {

            GeneratePostJob::dispatch($postTitle);

            Cache::forget('posts.all'); // Clears the cache for all posts
            $this->info('Post generation queued for: ' . $postTitle->title);
            Log::info('Post generation queued for: ' . $postTitle->title);
        }
    }
}
