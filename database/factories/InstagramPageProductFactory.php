<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramPageProduct>
 */
class InstagramPageProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'follower_count' => fn()=> $this->faker->numberBetween(1000, 1500000),
            'username' => fn()=> $this->faker->unique()->userName(),
            'following_count' => fn()=> $this->faker->numberBetween(1000, 1500000),
            'is_visible' => fn()=> $this->faker->boolean(),
            'bio' => fn()=> $this->faker->text(10),
            'posts_count' => fn()=> $this->faker->numberBetween(10, 500),
        ];
    }
}
