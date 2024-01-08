<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $usersWithOrder = DB::select('
SELECT * FROM users INNER JOIN orders ON (users.id = orders.user_id) WHERE (orders.status = "uncompleted")');
        dd($usersWithOrder);
    }

    public function test_two()
    {
        # Display all products and their associated categories. Include products without categories.
        $products = Product::factory(20)->create();
        Category::factory(20)->create();
        $products->each(function (Product $product) {
            ProductCategory::factory()->create([
                'category_id' => $product->id,
                'product_id' => $product->id,
            ]);
        });
        Product::factory(20)->create();

        $productsWithCategory = DB::select('SELECT * FROM products LEFT OUTER JOIN (product_category,categories)
    ON (products.id = product_category.product_id AND product_category.category_id = categories.id)');
        dd($productsWithCategory);
    }
}
