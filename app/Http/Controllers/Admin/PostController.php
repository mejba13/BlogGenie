<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\NewPostNotification;
use App\Services\OpenAIService;
use App\Mail\PostCreatedMail;
use App\Mail\PostFailedMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PostController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display post creation form.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store the post created via form submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $title = $request->input('title');
        $user_id = Auth::id();

        try {
            // Step 1: Generate post data using OpenAI
            $postData = $this->openAIService->generatePostData($title);

            // Step 2: Create and save the post
            $post = Post::create([
                'user_id' => $user_id,
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'content' => $postData['content'],
                'status' => 'published',
                'published_at' => now(),
                'featured_image_url' => $postData['featured_image_url'] ?? null,  // Image URL
                'video_url' => $postData['video_url'] ?? null,  // Video URL
            ]);

            // Step 3: Attach Categories
            $this->attachCategories($post, $postData['categories']);

            // Step 4: Attach Tags
            $this->attachTags($post, $postData['tags']);

            // Step 5: Add Meta Data
            $this->addMetaData($post, $postData);

            // Step 6: Send Discord Notification
            $post->notify(new NewPostNotification($post));

            // Fetch the authenticated user
            $user = User::findOrFail($user_id);

            // Step 7: Send Email Notification
            Mail::to($user->email)->send(new PostCreatedMail($post));

            // Clear Cache
            Cache::forget('posts.all');

            return redirect()->route('admin.posts.create')->with('success', 'Post created successfully.');

        } catch (Exception $e) {
            Log::error('Post creation failed: ' . $e->getMessage());

            // Fetch the authenticated user
            $user = User::findOrFail($user_id);

            // Send Failure Email
            Mail::to($user->email)->send(new PostFailedMail($title, $e->getMessage()));

            return redirect()->route('admin.posts.create')->withErrors('Failed to create post: ' . $e->getMessage());
        }
    }

    /**
     * Display the list of posts.
     */
    public function index()
    {
        // Cache posts for 10 minutes (600 seconds)
        $posts = Cache::remember('posts.all', 600, function () {
            return Post::with('categories', 'tags')->orderBy('published_at', 'desc')->paginate(10);
        });

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Display a single post.
     */
    public function show($id)
    {
        // Cache individual post for 1 hour
        $post = Cache::remember("post.$id", 3600, function () use ($id) {
            return Post::with('categories', 'tags', 'meta')->findOrFail($id);
        });

        // Extract meta title and description
        $metaTitle = $post->meta()->where('meta_key', 'meta_title')->value('meta_value');
        $metaDescription = $post->meta()->where('meta_key', 'meta_description')->value('meta_value');

        return view('admin.posts.show', compact('post', 'metaTitle', 'metaDescription'));
    }

    /**
     * Edit post.
     */
    public function edit($id)
    {
        $post = Post::with('categories', 'tags')->findOrFail($id);
        $categories = Category::all();

        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update post data.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image validation
            'status' => 'required|in:draft,published,archived', // Status validation
        ]);

        $post = Post::findOrFail($id);

        // Update featured image if uploaded
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('featured_images', 'public');
            $post->featured_image_url = $imagePath;
        }

        // Update post data
        $post->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'video_url' => $request->input('video_url') ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819',  // Fallback video URL
            'status' => $request->input('status'),
        ]);

        // Update Categories and Tags
        $this->attachCategories($post, $request->input('categories', []));
        $this->attachTags($post, $request->input('tags', []));

        // Clear Cache
        Cache::forget('posts.all');

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    /**
     * Delete post.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        // Clear cache for all posts
        Cache::forget('posts.all');

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * Attach categories to the post.
     *
     * @param Post $post
     * @param array $categories
     */
    private function attachCategories(Post $post, $categories)
    {
        if (!empty($categories)) {
            $categoryIds = [];
            foreach ($categories as $categoryName) {
                $categoryName = trim($categoryName);
                if (!empty($categoryName)) {
                    $category = Category::firstOrCreate([
                        'name' => $categoryName,
                        'slug' => Str::slug($categoryName),
                    ]);
                    $categoryIds[] = $category->id;
                }
            }
            $post->categories()->sync($categoryIds);
        }
    }

    /**
     * Attach tags to the post.
     *
     * @param Post $post
     * @param array $tags
     */
    private function attachTags(Post $post, $tags)
    {
        // Check if tags is a string, and if so, convert it to an array
        if (is_string($tags)) {
            $tags = explode(',', $tags);  // Convert comma-separated string to array
        }

        if (!empty($tags) && is_array($tags)) {
            $tagIds = [];

            foreach ($tags as $tagName) {
                $tagName = trim($tagName);  // Trim any spaces around tag names

                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate([
                        'name' => $tagName,
                        'slug' => Str::slug($tagName),
                    ]);

                    $tagIds[] = $tag->id;
                }
            }

            // Sync tags with the post
            $post->tags()->sync($tagIds);
        }
    }

    /**
     * Add meta data to the post.
     *
     * @param Post $post
     * @param array $postData
     */
    private function addMetaData(Post $post, $postData)
    {
        PostMeta::create([
            'post_id' => $post->id,
            'meta_key' => 'meta_title',
            'meta_value' => $postData['title'],
        ]);

        PostMeta::create([
            'post_id' => $post->id,
            'meta_key' => 'meta_description',
            'meta_value' => substr(strip_tags($postData['content']), 0, 150),
        ]);
    }
}
