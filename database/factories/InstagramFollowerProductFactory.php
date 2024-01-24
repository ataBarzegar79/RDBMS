<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramFollowerProduct>
 */
class InstagramFollowerProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price_per_follower' => fn() => $this->faker->unique()->randomNumber(),
            'provider_name' => fn() => $this->faker->unique()->name,
            'service_quality' => fn() => $this->faker->randomNumber([1,10]),
        ];
    }
}
