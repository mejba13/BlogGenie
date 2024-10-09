<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostMeta;

class PostMetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 post meta records
        PostMeta::factory()->count(10)->create();
    }
}
