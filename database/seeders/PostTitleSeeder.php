<?php

namespace Database\Seeders;

use App\Models\PostTitle;
use Illuminate\Database\Seeder;

class PostTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate 10 sample PostTitles using the factory
        PostTitle::factory()->count(10)->create();
    }
}
