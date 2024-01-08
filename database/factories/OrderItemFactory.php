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
            'price' => fn() => $this->faker->randomFloat(2, 10, 50),
            'quantity' => fn() => $this->faker->numberBetween(1, 10),
            'order_id' => fn() => Order::factory()->create(),
            'product_id' => fn() => Product::factory()->create(),
        ];
    }

    public function withProduct($product): OrderItemFactory
    {
        return $this->state(
            [
                'product_id' => $product->id
            ]
        );
    }

    public function withOrder($order): OrderItemFactory
    {
        return $this->state(
            [
                'order_id' => $order->id
            ]
        );
    }
}
