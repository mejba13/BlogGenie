<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;

class CategoryPostSeeder extends Seeder
{
    public function run(): void
    {
        // Get all posts
        $posts = Post::all();

        // Attach 1 to 3 random categories to each post
        $posts->each(function ($post) {
            $categories = Category::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $post->categories()->attach($categories);
        });
    }
}
