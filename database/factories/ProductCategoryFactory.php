<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
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
            'category_id' => fn() => $this->faker->unique()->randomNumber(),
            'product_id' => fn() => $this->faker->unique()->randomNumber(),
        ];
    }

    public function withCategory(Category $category): ProductCategoryFactory
    {
        return $this->state(
            [
                'category_id' => $category
            ]
        );
    }

    public function withProduct(Product $product): ProductCategoryFactory
    {
        return $this->state(
            [
                'product_id' => $product
            ]
        );
    }
}
