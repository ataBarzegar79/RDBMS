<?php

namespace Database\Factories;

use App\Models\InstagramFollower;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramFollower>
 */
class InstagramFollowerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->create(['productable_type' => InstagramFollower::class]),
            'price_per_followers' => $this->faker->randomFloat(min: 1, max: 20),
            'provider_name' => $this->faker->name,
            'service_quality' => $this->faker->numberBetween(1,10),
        ];
    }
}
