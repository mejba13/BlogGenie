<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,  // Seed 10 users
            PostSeeder::class,  // Seed 10 posts
            CategorySeeder::class,  // Seed 10 categories
            TagSeeder::class,  // Seed 10 tags
            PostTagSeeder::class,  // Assign tags to posts
            PostMetaSeeder::class,  // Seed 10 post meta
            CommentSeeder::class,  // Seed 10 comments
            CategoryPostSeeder::class,  // Assign categories to posts
        ]);
    }
}
