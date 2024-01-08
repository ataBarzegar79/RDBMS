<?php

namespace Database\Factories;

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
        return [
            'price' => fn() => $this->faker->randomFloat(2, 10, 50),
            'producible_id' => fn() => $this->faker->unique()->numberBetween(1, 10000),
            'producible_type' => fn() => $this->faker->unique()->name,
            'is_active' => fn() => $this->faker->randomNumber([0, 1]),
            'quantity' => fn() => $this->faker->randomNumber(),
        ];
    }

    function withProducible(int $id, string $type): ProductFactory
    {
        return $this->state([
            'producible_id' => $id,
            'producible_type' => $type
        ]);
    }

    public function withProductType(string $type): ProductFactory
    {
        return $this->state([
            'producible_type' => $type
        ]);
    }
}
