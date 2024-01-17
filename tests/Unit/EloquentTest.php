<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\InstagramFollowerProduct;
use App\Models\InstagramPageProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Tests\TestCase;

class EloquentTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    #[NoReturn] public function test_one()
    {
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();

        $query = Order::query()
            ->select('users.*','orders.status')
            ->join('users','orders.user_id','=','users.id')
            ->where('orders.status','=','uncompleted');
        dd($query->get());
    }

    #[NoReturn] public function test_two()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();
        $query = Product::query()
            ->select('products.*','categories.*')
            ->leftJoin('product_category','product_category.product_id','=','products.id')
            ->leftJoin('categories','product_category.category_id','=','categories.id');
        dd($query->get());
    }

    #[NoReturn] public function test_three()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = Category::query()
            ->select('categories.id', 'categories.name'
                , DB::raw(' COUNT(product_category.product_id) AS product_count'))
            ->leftJoin('product_category', 'product_category.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name');

        dd($query->get());
    }

    #[NoReturn] public function test_four()
    {

        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        OrderItem::factory(5)->withProduct($product)->create();

        $query = Product::query()
            ->select('products.producible_id','products.producible_type'
                ,DB::raw('SUM(order_items.price * order_items.quantity) AS  revenue'))
            ->leftJoin('order_items','products.id','=','order_items.product_id')
            ->leftJoin('orders','orders.id','=','order_items.order_id')
            ->where("products.producible_type" ,'=', 'App\\Models\\InstagramFollowerProduct')
            ->where('orders.status','=','completed')
            ->groupBy('products.producible_id','products.producible_type');
        dd($query->get());
    }

    #[NoReturn] public function test_five()
    {
        $product = Product::factory()->create();
        Review::factory()->withReviewable($product)->count(10)->create();

        $query = Product::query()
            ->select('products.id','products.price','reviews.reviewable_type'
                ,DB::raw('AVG(reviews.rate) AS average_rating'))
            ->leftJoin('reviews','products.id','=','reviews.reviewable_id')
            ->where('reviews.reviewable_type','=','App\\Models\\Product')
            ->groupBy('products.id','products.price','reviews.reviewable_type')
            ->orderByDesc('average_rating')
            ->limit(5);

        dd($query->get());
    }

    #[NoReturn] public function test_six()
    {
        $product = Product::factory()->create();
        Review::factory()->withReviewable($product)->count(5)->create();

        $query = Product::query()
            ->select('products.*','reviews.*')
            ->join('reviews','reviews.reviewable_id','=','products.id')
            ->where('reviews.reviewable_type','=','App\\Models\\Product');

        dd($query->get());
    }

    #[NoReturn] public function test_seven()
    {
        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        OrderItem::factory(5)->withProduct($product)->create();

        $query = User::query()
            ->select('users.*','producible_type')
            ->join('orders','orders.user_id','=','users.id')
            ->join('order_items','orders.id','=','order_items.order_id')
            ->join('products','order_items.product_id','=','products.id')
            ->where('products.producible_type','=','App\\Models\\InstagramFollowerProduct')
            ->where('order_items.created_at', '>', Carbon::now()->subMonth());

        dd($query->get());
    }

    #[NoReturn] public function test_eight()
    {
        $category = Category::factory()->create();
        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        Product::query()
            ->join('product_category', 'product_category.product_id', '=', 'products.id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->where('products.producible_type', '=', 'App\\Models\\InstagramFollowerProduct')
            ->update(['price' => DB::raw('price * 100')]);

        dd(Product::query()->get());
    }

    #[NoReturn] public function test_nine()
    {
        User::factory()->create();
        OrderItem::factory(5)->create();

        User::query()
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.id')
            ->orWhere('order_items.created_at', '<', now()->subDays(7))
            ->delete();

        dd(User::query()->get());
    }

    #[NoReturn] public function test_ten()
    {
        # Insert a User and an order with one page and two follower product.
        User::query()
            ->insert([
                'name' => 'ali',
                'email' => 'ali@gmail.com',
                'password' => bcrypt(32462567),
            ]);

        InstagramFollowerProduct::query()
            ->insert([
                [
                    'price_per_follower' => 35,
                    'provider_name' => 'kazem',
                    'service_quality' => 1,
                ],
                [
                    'price_per_follower' => 6,
                    'provider_name' => 'yashar',
                    'service_quality' => 4,
                ],
            ]);

        InstagramPageProduct::query()
            ->insert([
                [
                    'follower_count' => 10,
                    'username' => 'mohammad',
                    'following_count' => 10,
                    'is_visible' => true,
                    'posts_count' => 10,
                ],
            ]);

        Product::query()
            ->insert([
                [
                    'price' => 10,
                    'producible_id' => InstagramPageProduct::first()->id,
                    'producible_type' => InstagramPageProduct::class
                ],
            ]);

        Order::query()
            ->insert([
                [
                    'status' => 'completed',
                    'user_id' => 1,
                ],
            ]);

        OrderItem::query()
            ->insert([
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'price' => 39,
                    'order_id' => 1,
                ],
            ]);
        dd(Order::get());
    }

    public function test_eleven()
    {

        $products = Product::factory()->create();
        Review::factory()->withReviewable($products)->count(5)->create();

        Product::query()
            ->update([
                'price' => DB::raw('
            CASE
                WHEN (
                    SELECT AVG(rate) FROM reviews
                    WHERE reviewable_id = products.id AND reviewable_type = "App\\Models\\Product"
                ) > 8 THEN price * 1.1
                WHEN (
                    SELECT AVG(rate) FROM reviews
                    WHERE reviewable_id = products.id AND reviewable_type = "App\\Models\\Product"
                ) < 4 THEN price * 0.9
                ELSE price
            END
        ')
            ]);
        dd(Product::query()->get());
    }
}
