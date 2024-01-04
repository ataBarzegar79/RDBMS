<?php

namespace Database\Factories;

use App\Models\InstagramPage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramPage>
 */
class InstagramPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->create(['productable_id' => 1]),
            'followers' => $this->faker->numberBetween(),
            'following' => $this->faker->numberBetween(),
            'visibility' => $this->faker->boolean,
            'username' => fake()->unique()->name,
            'bio' => $this->faker->sentence,
        ];
    }
}
