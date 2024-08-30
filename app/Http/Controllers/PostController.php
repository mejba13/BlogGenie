<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostMeta;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;
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
            $postData = $this->openAIService->generatePostData($title);

            $post = Post::create([
                'user_id' => $user_id,
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'content' => $postData['content'],
                'status' => 'published',
                'published_at' => now(),
            ]);

            foreach ($postData['categories'] as $categoryName) {
                $category = Category::firstOrCreate([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                ]);
                $post->categories()->attach($category->id);
            }

            foreach ($postData['tags'] as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                    'slug' => Str::slug($tagName),
                ]);
                $post->tags()->attach($tag->id);
            }

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

            return redirect()->route('posts.create')->with('success', 'Post generated and saved successfully!');

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
        // Retrieve all posts from the database
        $posts = Post::all();

        // Pass the posts to the view
        return view('posts.index', compact('posts'));
    }
}
