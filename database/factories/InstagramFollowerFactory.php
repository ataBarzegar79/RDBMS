<?php

namespace Database\Factories;

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
            'price_per_follower' => $this->faker->numberBetween(),
            'provider_name' => $this->faker->numberBetween(),
            'service_quality' => $this->faker->numberBetween(),
            'speed_of_follower_charging' => $this->faker->time
        ];
    }
}
