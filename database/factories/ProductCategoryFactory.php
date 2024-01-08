<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => fn()=> $this->faker->unique()->randomNumber(),
            'product_id' => fn()=> $this->faker->unique()->randomNumber(),
        ];
    }

    public function withIds(int $productId,int $categoryId): ProductCategoryFactory
    {
        return $this->state(
            [
                'product_id' => $productId,
                'category_id' => $categoryId
            ]
        );
    }
}
