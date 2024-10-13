<?php

namespace Database\Seeders;

use App\Models\PostMeta;
use Illuminate\Database\Seeder;

class PostMetaSeeder extends Seeder
{
    public function run(): void
    {
        PostMeta::factory()->count(10)->create();  // Ensures only 10 post meta entries are created
    }
}
