<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostTitle;

class PostTitleSeeder extends Seeder
{
    public function run(): void
    {
        PostTitle::factory()->count(10)->create();
    }
}
