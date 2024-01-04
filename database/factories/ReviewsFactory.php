<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReviewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reviewable_type' => $this->faker->randomElement([Category::class, Product::class]),
            'reviewable_id' => $this->faker->randomElement([Category::factory(), Product::factory()]),
            'content' => $this->faker->text,
            'rate' => $this->faker->numberBetween()
        ];
    }
}
