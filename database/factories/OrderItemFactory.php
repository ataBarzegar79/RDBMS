<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => fn() => $this->faker->randomNumber(),
            'quantity' => fn() => $this->faker->randomNumber(),
            'order_id' => fn() => Order::factory()->create(),
            'product_id' => fn() => Product::factory()->create(),
        ];
    }

    public function withProduct(Product $product)
    {
        return $this->state(
            [
                'product_id' => $product,
            ]
        );
    }

    public function withOrder(Order $order)
    {
        return $this->state(
            [
                'product_id' => $order,
            ]
        );
    }
}
