<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    // Method to show posts on the welcome page
    public function index()
    {
        // Fetch paginated posts, ordering by published_at
        $posts = Post::orderBy('published_at', 'desc')->paginate(10);

        // Return the welcome view with posts data
        return view('welcome', compact('posts'));
    }
}
