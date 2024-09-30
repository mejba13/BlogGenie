<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\PostTitle;
use App\Models\Tag;
use App\Services\OpenAIService;
use App\Notifications\NewPostNotification;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PostCreatedMail;
use App\Mail\PostFailedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class GeneratePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $postTitle;

    /**
     * Create a new job instance.
     *
     * @param PostTitle $postTitle
     */
    public function __construct(PostTitle $postTitle)
    {
        $this->postTitle = $postTitle;
    }

    /**
     * Execute the job.
     *
     * @param OpenAIService $openAIService
     */
    public function handle(OpenAIService $openAIService)
    {
        try {
            // Step 1: Generate Post Data
            $postData = $openAIService->generatePostData($this->postTitle->title);

            // Step 2: Create a new Post record
            $post = Post::create([
                'user_id' => 1,  // Assuming the admin user ID is 1
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'content' => $postData['content'],
                'status' => 'published',
                'published_at' => $this->postTitle->publish_date,
                'featured_image_url' => $postData['featured_image_url'],
                'video_url' => $postData['video_url'],
            ]);

            // Step 3: Attach categories and tags
            $this->attachCategoriesAndTags($post, $postData);

            // Step 4: Create Meta Data
            PostMeta::create([
                'post_id' => $post->id,
                'meta_key' => 'meta_title',
                'meta_value' => $postData['title'],
            ]);

            PostMeta::create([
                'post_id' => $post->id,
                'meta_key' => 'meta_description',
                'meta_value' => substr($postData['content'], 0, 150),
            ]);

            // Step 5: Notify Discord
            $post->notify(new NewPostNotification($post));

            Cache::forget('posts.all'); // Clears the cache for all posts

            // Step 6: Send Success Email
            Mail::to('mejba.13@gmail.com')->send(new PostCreatedMail($post));

            Log::info("Post created successfully: " . $post->title);

        } catch (Exception $e) {
            Log::error('Post generation failed: ' . $e->getMessage());

            // Send Failure Email
            Mail::to('mejba.13@gmail.com')->send(new PostFailedMail($this->postTitle->title, $e->getMessage()));
        }
    }

    private function attachCategoriesAndTags(Post $post, $postData)
    {
        // Attach Categories
        if (!empty($postData['categories'])) {
            $categoryIds = [];
            foreach ($postData['categories'] as $categoryName) {
                // Use firstOrCreate to avoid inserting duplicate categories
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName)],  // Check for existing slug
                    ['name' => $categoryName]  // If not exists, create with name
                );
                $categoryIds[] = $category->id;
            }
            $post->categories()->sync($categoryIds);  // Attach categories
        }

        // Attach Tags
        if (!empty($postData['tags'])) {
            $tagIds = [];
            foreach ($postData['tags'] as $tagName) {
                // Use firstOrCreate to avoid inserting duplicate tags
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],  // Check for existing slug
                    ['name' => $tagName]  // If not exists, create with name
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);  // Attach tags
        }
    }
}
