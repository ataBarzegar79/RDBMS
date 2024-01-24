<?php

namespace Database\Factories;

use App\Models\InstagramFollowerProduct;
use App\Models\InstagramPageProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use PHPUnit\Event\Code\Test;

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
        $str = InstagramPageProduct::class;
        $rand = rand(1, 2);
        if ($rand === 1)
            $str = InstagramFollowerProduct::class;
        return [
            'price' => fn() => $this->faker->randomFloat(2, 10, 50),
            'producible_id' => fn() => $this->faker->unique()->numberBetween(1, 10000),
            'producible_type' => $str,
            'is_active' => fn() => $this->faker->randomNumber([0, 1]),
            'quantity' => fn() => $this->faker->randomNumber(),
        ];
    }

    public function withProducible_type(string $type): ProductFactory
    {
        return $this->state
        ([
            "producible_type" => $type,
        ]);
    }
}
