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
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        // Show the form to create a new category
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }


    public function edit(Category $category)
    {
        // Show the form to edit a category
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        // Update the category
        $category->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
        ]);

        // Redirect to the category list with a success message
        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }
    public function destroy(Category $category)
    {
        // Delete the category
        $category->delete();

        // Redirect to the category list with a success message
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
