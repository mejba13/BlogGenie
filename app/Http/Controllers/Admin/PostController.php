<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PostCreatedMail;
use App\Mail\PostFailedMail;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostMeta;
use App\Notifications\NewPostNotification;
use App\Notifications\PostFailedNotification;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Str;

class PostController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Show the form to create a new post.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store the newly created post in the database.
     */
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
                'featured_image_url' => $postData['featured_image_url'],
                'video_url' => $postData['video_url'],
            ]);

            // Step 3: Attach Categories to the Post
            if (!empty($postData['categories'])) {
                foreach ($postData['categories'] as $categoryName) {
                    $categoryName = trim($categoryName);
                    if (!empty($categoryName)) {
                        $category = Category::firstOrCreate([
                            'name' => $categoryName,
                            'slug' => Str::slug($categoryName),
                        ]);
                        $post->categories()->attach($category->id);
                    }
                }
            }

            // Step 4: Attach Tags to the Post
            if (!empty($postData['tags'])) {
                foreach ($postData['tags'] as $tagName) {
                    $tagName = trim($tagName);
                    if (!empty($tagName)) {
                        $tag = Tag::firstOrCreate([
                            'name' => $tagName,
                            'slug' => Str::slug($tagName),
                        ]);
                        $post->tags()->attach($tag->id);
                    }
                }
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

            // Step 7: Send Success Email
            Mail::to(Auth::user()->email)->send(new PostCreatedMail($post));

            // Clear the cache after a new post is created
            Cache::forget('posts.all');

            return redirect()->route('admin.posts.create')->with('success', 'Post created successfully.');

        } catch (Exception $e) {
            Log::error('Failed to generate or save post: ' . $e->getMessage());

            // Step 8: Send Failure Notification to Discord
            Auth::user()->notify(new PostFailedNotification($title, $e->getMessage()));

            // Send Failure Email to the authenticated user
            Mail::to(Auth::user()->email)->send(new PostFailedMail($title, $e->getMessage()));

            return redirect()->route('admin.posts.create')->withErrors('Failed to generate or save post. Please try again.');
        }
    }

    /**
     * Display the form for editing an existing post.
     */
    public function edit($id)
    {
        // Find the post by ID, or fail if not found
        $post = Post::with('categories', 'tags')->findOrFail($id);

        // Get all categories for the form
        $categories = Category::all();

        // Return the edit view and pass the post and categories data to it
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified post in the database.
     */
    public function update(Request $request, $id)
    {
        // Find the post by its ID, or fail if not found
        $post = Post::findOrFail($id);

        // Validate the request inputs
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Check if a new image is uploaded
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('featured_images', 'public');
            $post->featured_image_url = $imagePath;
        }

        // Update post data (including video_url and status)
        $post->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'video_url' => $request->input('video_url'),
            'status' => $request->input('status'),
        ]);

        // Update categories if present
        if ($request->has('categories')) {
            $post->categories()->sync($request->input('categories'));
        }

        // Update tags if present
        if ($request->has('tags')) {
            $tags = array_map('trim', explode(',', $request->input('tags')));
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName], ['slug' => Str::slug($tagName)]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        // Clear the cache after the post is updated
        Cache::forget('posts.all');

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    /**
     * Display a listing of posts.
     */
    public function index()
    {
        // Cache the posts listing for 10 minutes (600 seconds)
        $posts = Cache::remember('posts.all', 600, function () {
            return Post::with('categories', 'tags')->orderBy('published_at', 'desc')->paginate(10);
        });

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Display the specified post.
     */
    public function show($id)
    {
        // Cache the post data for 1 hour (3600 seconds)
        $post = Cache::remember("post.$id", 3600, function () use ($id) {
            return Post::with('categories', 'tags', 'meta')->findOrFail($id);
        });

        // Extract meta title and description
        $metaTitle = $post->meta()->where('meta_key', 'meta_title')->value('meta_value');
        $metaDescription = $post->meta()->where('meta_key', 'meta_description')->value('meta_value');

        return view('admin.posts.show', compact('post', 'metaTitle', 'metaDescription'));
    }

    /**
     * Remove the specified post from the database.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        // Clear the cache after the post is deleted
        Cache::forget('posts.all');

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }
}
