<?php

namespace Database\Factories;

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
            'followers_count' => $this->faker->numberBetween(),
            'following_count' => $this->faker->numberBetween(),
            'post_count' => $this->faker->numberBetween(),
            'visibility' => $this->faker->numberBetween(),
            'username' => $this->faker->name,
            'bio' => $this->faker->slug
        ];
    }
}
