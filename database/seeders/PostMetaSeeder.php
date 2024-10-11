<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostMeta;

class PostMetaSeeder extends Seeder
{
    public function run(): void
    {
        PostMeta::factory()->count(10)->create();
    }
}
