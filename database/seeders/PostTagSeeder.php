<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostTagSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 posts and 5 tags
        $posts = Post::factory(10)->create();
        $tags = Tag::factory(5)->create();

        // Attach tags to posts in the pivot table
        $posts->each(function ($post) use ($tags) {
            // Attach between 1 to 3 random tags to each post
            $post->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
