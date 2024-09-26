<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        // List all tags
        $tags = Tag::all();
        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        // Show the form to create a new tag
        return view('admin.tags.create');
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

    public function edit(Tag $tag)
    {
        // Show the form for editing the tag
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {

        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'slug' => 'required|string|max:255|unique:tags,slug,' . $tag->id,
        ]);

        // Update the tag
        $tag->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        // Redirect to the tag list with a success message
        return redirect()->route('tags.index')->with('success', 'Tag updated successfully!');
    }

    public function destroy(Tag $tag)
    {
        // Delete the tag
        $tag->delete();

        // Redirect to the tag list with a success message
        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully!');
    }
}
