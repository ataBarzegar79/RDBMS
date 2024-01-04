<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FollowerProduct>
 */
class FollowerProductFactory extends Factory
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
            "price_per_follower" => fake()->randomFloat(),
            "provider_name" => fake()->unique()->name(),
            "service_quantity" => fake()->randomDigit(),
            "follower_charge_speed" => fake()->shuffleString,
        ];
    }
}
