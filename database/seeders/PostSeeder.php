<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure only 10 posts are created
        Post::factory()->count(10)->create();
    }
}
