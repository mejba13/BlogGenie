<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        // List all categories
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        // Show the form to create a new category
        return view('categories.create');
    }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        // Create a new category
        Category::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
        ]);

        // Redirect to the category list with a success message
        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }
}
