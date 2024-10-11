<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        $title = $this->faker->sentence;

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(3, true),
            'featured_image_url' => $this->faker->imageUrl(),
            'video_url' => $this->faker->optional()->url,
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'publish_date' => $this->faker->optional()->dateTimeThisYear(),
            'published_at' => now(),
        ];
    }
}
