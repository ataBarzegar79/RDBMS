<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function withProductCategory(Product $product, Category $category): ProductCategoryFactory|Factory
    {
        return $this->state
        ([
           "category_id" =>  $category,
            "product_id" => $product
        ]);
    }
}
