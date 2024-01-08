<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Factories\OrderItemFactory;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        # orders with specific user
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        dd($user->orders()->count());
    }
    public function test_one()
    {
        # Retrieve user details along with the uncompleted orders they have placed.
        $user = User::factory()->create();
        $orders = Order::factory()->withUser($user)->count(15)->create();
        $orders->each(function (Order $order) {
            OrderItem::factory()->create(['order_id' => $order->id]);
        });
        $users = DB::select('
SELECT * FROM users LEFT JOIN orders ON (users.id = orders.user_id) LEFT JOIN order_items ON (orders.id = order_items.order_id) WHERE (orders.status = "uncompleted")');
        dd($users);
    }
}
