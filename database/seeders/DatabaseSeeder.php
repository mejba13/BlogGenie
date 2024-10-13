<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure that each table gets only 10 rows of data
        $this->call([
            UserSeeder::class,       // 10 users
            CategorySeeder::class,   // 10 categories
            PostSeeder::class,       // 10 posts
            TagSeeder::class,        // 10 tags
            PostTagSeeder::class,    // 10 tags associated with posts
            CategoryPostSeeder::class, // 10 categories associated with posts
            CommentSeeder::class,    // 10 comments
            PostMetaSeeder::class,   // 10 post meta entries
            PostTitleSeeder::class,  // 10 post titles
        ]);
    }
}
