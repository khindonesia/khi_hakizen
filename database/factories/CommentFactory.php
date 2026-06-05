<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'body' => $this->faker->paragraph,
            'parent_id' => null,
            'commentable_type' => \App\Models\Post::class,
            'commentable_id' => 1,
        ];
    }
}
