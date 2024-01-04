<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reviewableType = $this->faker->randomElement([Product::class, Category::class]);
        return [
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph,
            'rate' => $this->faker->numberBetween(0,10),
            'reviewable_id' => $reviewableType::factory(),
            'reviewable_type' => $reviewableType,
        ];
    }
}
