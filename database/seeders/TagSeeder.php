<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure only 10 tags are created
        Tag::factory()->count(10)->create();
    }
}
