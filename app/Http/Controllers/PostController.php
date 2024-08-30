<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Exception;

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
        // Validate the input
        $request->validate([
            'title' => 'required|string',
            'slug' => 'required|string|unique:posts,slug',
        ]);

        // Get the title and slug from the request
        $title = $request->input('title');
        $slug = $request->input('slug');
        $user_id = Auth::id(); // Get the authenticated user's ID

        try {
            // Use the OpenAIService to generate the post content
            $content = $this->openAIService->generateContent($title, $slug);

            // Check if the content is present
            if (empty($content)) {
                throw new Exception('OpenAI API failed to generate content.');
            }

            // Log the content before saving
            Log::info('Generated content', ['title' => $title, 'slug' => $slug, 'content' => $content]);

            // Create and save the post
            $post = Post::create([
                'user_id' => $user_id,
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'status' => 'published', // Set status to 'published' by default
                'published_at' => now(),  // Set the current time as the published time
            ]);

            // Check if the post was actually saved
            if (!$post) {
                throw new Exception('Failed to save the post.');
            }

            // Redirect back to the form with a success message
            return redirect()->route('posts.create')->with('success', 'Post generated and saved successfully!');

        } catch (Exception $e) {
            // Log the error and notify via email
            Log::error('Failed to generate or save blog post: ' . $e->getMessage());

            Mail::raw('Failed to generate or save content for the title: ' . $title . ' and slug: ' . $slug, function ($message) {
                $message->to('admin@example.com')
                    ->subject('Post Generation and Saving Failed');
            });

            return redirect()->route('posts.create')->withErrors('Failed to generate or save post content. Please try again or check your email for more information.');
        }
    }

    public function index()
    {
        // Retrieve all posts
        $posts = Post::orderBy('published_at', 'desc')->get();
        return view('posts.index', compact('posts'));
    }

    public function show($id)
    {
        // Show a single post with its categories, tags, and metadata
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }
}
