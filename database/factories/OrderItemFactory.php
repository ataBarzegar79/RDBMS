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
            'price' => rand(50, 1000),
            'quantity' => fn() => $this->faker->randomNumber(),
            'order_id' => fn() => Order::factory()->create(),
            'product_id' => fn() => Product::factory()->create(),
        ];
    }

    public function withOrder(Order $order): OrderItemFactory
    {
        return $this->state
        ([
           "order_id" => $order
        ]);
    }
    public function withProduct(Product $product): OrderItemFactory
    {
        return $this->state
        ([
           "product_id" => $product
        ]);
    }
}
