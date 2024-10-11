<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call each individual seeder
        $this->call([
            UserSeeder::class,          // Seeder for users
            CategorySeeder::class,      // Seeder for categories
            PostTitleSeeder::class,
            PostSeeder::class,          // Seeder for posts
            TagSeeder::class,           // Seeder for tags
            CommentSeeder::class,       // Seeder for comments
            PostMetaSeeder::class,      // Seeder for post meta
            CategoryPostSeeder::class,  // Seeder for post-category pivot table
            PostTagSeeder::class,       // Seeder for post-tag pivot table
        ]);
    }
}
