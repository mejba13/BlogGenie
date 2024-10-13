<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::all();

        // Attach categories to each post
        $posts->each(function ($post) {
            $categories = Category::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $post->categories()->attach($categories);
        });
    }
}
