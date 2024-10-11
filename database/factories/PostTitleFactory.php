<?php

namespace Database\Factories;

use App\Models\PostTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostTitleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = PostTitle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,  // Generate random title
            'publish_date' => $this->faker->date(),  // Generate random date
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
