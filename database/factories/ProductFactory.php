<?php

namespace Database\Factories;

use App\Models\InstagramFollower;
use App\Models\InstagramPage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(1,10),
            'quantity' => $this->faker->numberBetween(0,10000),
            'is_active' => $this->faker->boolean,
            'productable_id' => InstagramFollower::factory(),
            'productable_type' => InstagramFollower::class,
        ];
    }
}
