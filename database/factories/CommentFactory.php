<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),  // Create a post with PostFactory if not provided
            'user_id' => User::factory(),  // Create a user if not provided
            'content' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'spam']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
