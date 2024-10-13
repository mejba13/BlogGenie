<?php

namespace Database\Seeders;

use App\Models\PostTitle;
use Illuminate\Database\Seeder;

class PostTitleSeeder extends Seeder
{
    public function run(): void
    {
        PostTitle::factory()->count(10)->create();  // Ensures only 10 post titles are created
    }
}
