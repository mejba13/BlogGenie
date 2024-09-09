<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class DashboardController extends Controller
{
    public function index()
    {
        $postCount = Post::count();
        $categoryCount = Category::count();
        $tagCount = Tag::count();

        return view('dashboard', compact('postCount', 'categoryCount', 'tagCount'));
    }
}
