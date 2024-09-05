<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostMeta;
use App\Notifications\NewPostNotification;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $title = $request->input('title');
        $user_id = Auth::id();

        try {
            // Step 1: Generate Post Data using ChatGPT
            $postData = $this->openAIService->generatePostData($title);

            // Log the generated data
            Log::info("Generated Post Data: " . json_encode($postData));

            // Step 2: Create and Save the Post
            $post = Post::create([
                'user_id' => $user_id,
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'content' => $postData['content'],
                'status' => 'published',
                'published_at' => now(),
                'featured_image_url' => $postData['featured_image_url'],  // Save the featured image URL
                'video_url' => $postData['video_url'],  // Save the video URL
            ]);

            // Step 3: Attach Categories to the Post
            foreach ($postData['categories'] as $categoryName) {
                $category = Category::firstOrCreate([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                ]);
                $post->categories()->attach($category->id);
            }

            // Step 4: Attach Tags to the Post
            foreach ($postData['tags'] as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                    'slug' => Str::slug($tagName),
                ]);
                $post->tags()->attach($tag->id);
            }

            // Step 5: Add Meta Data to the Post
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

            // Step 6: Send Notification to Discord
            $post->notify(new NewPostNotification($post));


            return redirect()->route('posts.create')->with('success', 'Post created successfully.');

        } catch (Exception $e) {
            Log::error('Failed to generate or save post: ' . $e->getMessage());

            Mail::raw('Failed to generate or save post for title: ' . $title, function ($message) {
                $message->to('admin@example.com')->subject('Post Generation Failed');
            });

            return redirect()->route('posts.create')->withErrors('Failed to generate or save post. Please try again.');
        }
    }

    public function index()
    {
        // Retrieve all posts with their categories and tags
        $posts = Post::with('categories', 'tags')->orderBy('published_at', 'desc')->get();

        // Pass the posts to the view
        return view('posts.index', compact('posts'));
    }

    public function show($id)
    {
        $post = Post::with('categories', 'tags', 'meta')->findOrFail($id);

        // Extract meta title and description
        $metaTitle = $post->meta()->where('meta_key', 'meta_title')->value('meta_value');
        $metaDescription = $post->meta()->where('meta_key', 'meta_description')->value('meta_value');

        return view('posts.show', compact('post', 'metaTitle', 'metaDescription'));
    }

}
