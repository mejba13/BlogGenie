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
            UserSeeder::class,
            PostTitleSeeder::class,
            CategorySeeder::class,
            PostTagSeeder::class,
            PostMetaSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
