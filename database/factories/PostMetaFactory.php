<?php

namespace Database\Factories;

use App\Models\PostMeta;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostMetaFactory extends Factory
{
    protected $model = PostMeta::class;

    public function definition()
    {
        return [
            'post_id' => Post::factory(),
            'meta_key' => $this->faker->word,
            'meta_value' => $this->faker->sentence,
        ];
    }
}
