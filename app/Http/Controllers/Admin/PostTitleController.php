<?php

// app/Http/Controllers/PostTitleController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostTitle;
use Illuminate\Http\Request;

class PostTitleController extends Controller
{
    public function index()
    {
        $postTitles = PostTitle::paginate(10); // Pagination
        return view('admin.post_titles.index', compact('postTitles'));
    }

    public function create()
    {
        return view('admin.post_titles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publish_date' => 'required|date',
        ]);

        PostTitle::create($request->all());

        return redirect()->route('post_titles.index')->with('success', 'Post title created successfully.');
    }

    public function edit(PostTitle $postTitle)
    {
        return view('admin.post_titles.edit', compact('postTitle'));
    }

    public function update(Request $request, PostTitle $postTitle)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publish_date' => 'required|date',
        ]);

        $postTitle->update($request->all());

        return redirect()->route('post_titles.index')->with('success', 'Post title updated successfully.');
    }

    public function destroy(PostTitle $postTitle)
    {
        $postTitle->delete();

        return redirect()->route('post_titles.index')->with('success', 'Post title deleted successfully.');
    }
}
