<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PostCreatedMail;
use App\Mail\PostFailedMail;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\NewPostNotification;
use App\Services\OpenAIService;
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
            // Step 1: Generate Post Data using OpenAI
            $postData = $this->openAIService->generatePostData($title);

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
                'video_url' => $postData['video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819',  // Fallback video URL
            ]);

            // Step 3: Attach Categories to the Post
            if (!empty($postData['categories'])) {
                $categoryIds = [];
                foreach ($postData['categories'] as $categoryName) {
                    $category = Category::firstOrCreate([
                        'name' => trim($categoryName),
                        'slug' => Str::slug($categoryName),
                    ]);
                    $categoryIds[] = $category->id;
                }
                $post->categories()->sync($categoryIds);
            }

            // Step 4: Attach Tags to the Post
            if (!empty($postData['tags'])) {
                $tagIds = [];
                foreach ($postData['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate([
                        'name' => trim($tagName),
                        'slug' => Str::slug($tagName),
                    ]);
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
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

            // Step 7: Send email to the user
            $user = User::findOrFail($user_id);
            Mail::to($user->email)->send(new PostCreatedMail($post));

            // Clear the cache
            Cache::forget('posts.all');

            return redirect()->route('admin.posts.create')->with('success', 'Post created successfully.');

        } catch (Exception $e) {
            Log::error('Failed to generate or save post: ' . $e->getMessage());

            // Step 8: Send failure email
            $user = User::findOrFail($user_id);
            Mail::to($user->email)->send(new PostFailedMail($title, $e->getMessage()));

            return redirect()->route('admin.posts.create')->withErrors('Failed to generate or save post: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $posts = Cache::remember('posts.all', 600, function () {
            return Post::with('categories', 'tags')->orderBy('published_at', 'desc')->paginate(10);
        });

        return view('admin.posts.index', compact('posts'));
    }

    public function show($id)
    {
        $post = Cache::remember("post.$id", 3600, function () use ($id) {
            return Post::with('categories', 'tags', 'meta')->findOrFail($id);
        });

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

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('featured_images', 'public');
            $post->featured_image_url = $imagePath;
        }

        $post->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'video_url' => $request->input('video_url') ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819',
            'status' => $request->input('status'),
        ]);

        if ($request->has('categories')) {
            $post->categories()->sync($request->input('categories'));
        }

        if ($request->has('tags')) {
            $tags = array_map('trim', explode(',', $request->input('tags')));
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName], ['slug' => Str::slug($tagName)]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        Cache::forget('posts.all');

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        Cache::forget('posts.all');

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
