<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'user_id' => User::factory(),  // This assumes you have a User factory as well
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(3, true), // Generate paragraphs for content
            'featured_image_url' => $this->faker->imageUrl(640, 480, 'posts', true), // Generates random image URL
            'video_url' => $this->faker->optional()->url,  // Optional video URL, sometimes null
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'publish_date' => $this->faker->optional()->dateTimeThisYear(), // Random publish date or null
            'published_at' => $this->faker->optional()->dateTimeThisYear(), // Random publish timestamp or null
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
