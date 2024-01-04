<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageProduct>
 */
class PageProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "product_id" => Product::factory(),
            "username" => fake()->unique()->userName,
            "follower_count" => fake()->randomDigit(),
            "following_count" => fake()->randomDigit(),
            "bio" => fake()->paragraph,
            "posts_count" => fake()->randomDigit(),
            "visibility" => "Public",
        ];
    }
}
