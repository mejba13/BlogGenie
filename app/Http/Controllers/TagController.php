<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        // List all tags
        $tags = Tag::all();
        return view('tags.index', compact('tags'));
    }

    public function create()
    {
        // Show the form to create a new tag
        return view('tags.create');
    }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        // Create a new tag
        Tag::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        // Redirect to the tag list with a success message
        return redirect()->route('tags.index')->with('success', 'Tag created successfully!');
    }
}
