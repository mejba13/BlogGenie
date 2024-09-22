<?php

namespace App\Http\Controllers;

use App\Mail\PostCreatedMail;
use App\Mail\PostFailedMail;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostMeta;
use App\Notifications\NewPostNotification;
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

    public function create()
    {
        return view('admin.posts.create');
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
            Mail::to('mejba.13@gmail.com')->send(new PostCreatedMail($post));

            // Clear the cache after a new post is created
            Cache::forget('posts.all'); // Clears the cache for all posts

            return redirect()->route('posts.create')->with('success', 'Post created successfully.');

        } catch (Exception $e) {
            Log::error('Failed to generate or save post: ' . $e->getMessage());

            // Send Failure Email
            Mail::to('mejba.13@gmail.com')->send(new PostFailedMail($title, $e->getMessage()));

            return redirect()->route('posts.create')->withErrors('Failed to generate or save post. Please try again.');
        }
    }

    public function index()
    {
        // Cache the posts listing for 10 minutes (600 seconds)
        $posts = Cache::remember('posts.all', 600, function () {
            return Post::with('categories', 'tags')->orderBy('published_at', 'desc')->paginate(10);
        });

        return view('admin.posts.index', compact('posts'));
    }

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

    public function edit($id)
    {
        $post = Post::with('categories', 'tags')->findOrFail($id);
        $categories = Category::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Validate the image
        ]);

        // Check if a new image is uploaded
        if ($request->hasFile('featured_image')) {
            // Store the uploaded image in the 'public/featured_images' directory
            $imagePath = $request->file('featured_image')->store('featured_images', 'public');
            $post->featured_image_url = $imagePath;  // Update with the image path
        } else if ($request->input('featured_image_url')) {
            // If a URL is provided, use it
            $post->featured_image_url = $request->input('featured_image_url');
        }

        // Update Post data
        $post->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'video_url' => $request->input('video_url'),
        ]);

        // Update Categories
        if ($request->has('categories')) {
            $post->categories()->sync($request->input('categories'));
        }

        // Update Tags
        if ($request->has('tags')) {
            $tags = array_map('trim', explode(',', $request->input('tags')));
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        // Redirect back with success message
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }



    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }



}
