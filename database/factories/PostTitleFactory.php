<?php

namespace Database\Factories;

use App\Models\PostTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostTitleFactory extends Factory
{
    protected $model = PostTitle::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'publish_date' => now(),
        ];
    }
}
