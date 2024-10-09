<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostMetaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = PostMeta::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(), // Create a post with PostFactory if not provided
            'meta_key' => $this->faker->randomElement(['meta_title', 'meta_description', 'meta_keywords']),
            'meta_value' => $this->faker->text(50),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
