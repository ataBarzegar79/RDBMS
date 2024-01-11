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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EloquentTest extends TestCase
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
            OrderItem::factory()->withOrder($order)->create();
        });
        $usersWithOrder = User::join(
            'orders',
            'users.id',
            '=',
            'orders.user_id'
        )->where('orders.status', '=', 'uncompleted');
        dd($usersWithOrder->get());
    }

    public function test_two()
    {
        # Display all products and their associated categories. Include products without categories.
        $products = Product::factory(20)->create();
        $categories = Category::factory(20)->create();
        $products->each(function (Product $product) use ($categories) {
            $categories->each(function (Category $category) use ($product) {
                ProductCategory::factory()
                    ->withProduct($product)
                    ->withCategory($category)
                    ->create();
            });
        });
        Product::factory(20)->create();

        $productsWithCategory = Product::leftJoin(
            'product_category',
            'products.id',
            '=',
            'product_category.product_id'
        )->leftJoin(
            'categories',
            'product_category.category_id',
            '=',
            'categories.id'
        );
        dd($productsWithCategory->get());
    }

    public function test_three()
    {
        # Display all categories and the number of products in each category.
        $products = Product::factory(20)->create();
        $categories = Category::factory(20)->create();
        $products->each(function (Product $product) use ($categories) {
            $categories->each(function (Category $category) use ($product) {
                ProductCategory::factory()
                    ->withProduct($product)
                    ->withCategory($category)
                    ->create();
            });
        });
        $categoriesWithNumberProducts = Category::select(
            'name',
            'categories.id',
            DB::raw('COUNT(category_id) AS Products'),
        )
            ->leftJoin(
                'product_category',
                'categories.id',
                '=',
                'product_category.category_id',
            )->groupBy('categories.id');
        dd($categoriesWithNumberProducts->get());
    }

    public function test_four()
    {
        # Calculate the total revenue for each product type.
        $user = User::factory()->create();
        $orders = Order::factory()->withUser($user)->count(15)->create();
        $orders->each(function (Order $order) {
            OrderItem::factory()->withOrder($order)->create();
        });
        $totalRevenue = OrderItem::select(
            '*',
            DB::raw('order_items.price * order_items.quantity AS totalSell'),
        )->join(
            'orders',
            'order_items.order_id',
            '=',
            'orders.id'
        )->join(
            'products',
            'order_items.product_id',
            '=',
            'products.id'
        )->where(
            'status',
            '=',
            'completed'
        );
        dd($totalRevenue->get());
    }

    public function test_five()
    {
        # Retrieve 5 products with the highest average ratings, considering reviews.
        $products = Product::factory(10)->create();
        $products->each(function (Product $product) {
            Review::factory()->withReviewable($product)->create();
        });
        Review::factory(10)->withReviewable($products[0])->create();
        $topRateProducts = Product::select(
            'products.*',
            DB::raw('AVG(rate) as Avgrate'),
        )->join(
            'reviews',
            'products.id',
            '=',
            'reviews.reviewable_id',
        )->where(
            'reviewable_type',
            '=',
            'App\\Models\\Product'
        )->groupBy(
            'products.id',
        )->orderByDesc('Avgrate')
            ->limit(5);
        dd($topRateProducts->get());
    }

    public function test_six()
    {
        # Retrieve all reviews associated with
        # a specific product type (has many through)
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(function (InstagramFollowerProduct $instagramFollowerProduct) {
            Product::factory()->withProducible($instagramFollowerProduct)->create();
        });
        $instagramPage->each(function (InstagramPageProduct $instagramPageProduct) {
            Product::factory()->withProducible($instagramPageProduct)->create();
        });
        $products = Product::all();
        $products->each(function (Product $product) {
            Review::factory()->withReviewable($product)->create();
        });
        Review::factory()->withReviewable($products[0])->create();
        $reviewsSpecificProductType = Review::
        join(
            'products',
            'reviews.reviewable_id',
            '=',
            'products.id',
        )
            ->where(
                'reviews.reviewable_type',
                '=',
                'App\\Models\\Product'
            )
            ->where(
                'producible_type',
                '=',
                'App\\Models\\InstagramPageProduct',
            );
        dd($reviewsSpecificProductType->get());
    }

    public function test_seven()
    {
        # Retrieve users who have ordered a specific product during the last month.
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(function (InstagramFollowerProduct $instagramFollowerProduct) {
            Product::factory()->withProducible($instagramFollowerProduct)->create();
        });
        $instagramPage->each(function (InstagramPageProduct $instagramPageProduct) {
            Product::factory()->withProducible($instagramPageProduct)->create();
        });
        $user = User::factory()->create();
        $orders = Order::factory()->withUser($user)->count(15)->create();
        $orders->each(function (Order $order) {
            Product::all()->each(function (Product $product) use ($order) {
                OrderItem::factory()->create([
                    'order_id' => $order,
                    'product_id' => $product
                ]);
            });
        });
        $userOrderSpecificProduct = User::join(
            'orders',
            'users.id',
            '=',
            'orders.user_id'
        )->join(
            'order_items',
            'order_items.order_id',
            '=',
            'orders.id'
        )->join(
            'products',
            'products.id',
            '=',
            'order_items.product_id'
        )->where(
            'producible_type',
            '=',
            'App\\Models\\InstagramPageProduct'
        )->where(
            'orders.created_at',
            '>',
            Carbon::now()->subMonth()
        );
        dd($userOrderSpecificProduct->get());
    }

    public function test_eight()
    {
        # Increase the prices of all products in a specific category and with a certain product type.
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(
            function (InstagramFollowerProduct $instagramFollowerProduct) {
                Product::factory()
                    ->withProducible($instagramFollowerProduct)
                    ->create();
            });
        $instagramPage->each(
            function (InstagramPageProduct $instagramPageProduct) {
                Product::factory()
                    ->withProducible($instagramPageProduct)
                    ->create();
            });
        $products = Product::all();
        $categories = Category::factory(10)->create();
        $products->each(function (Product $product) use ($categories) {
            $categories->each(function (Category $category) use ($product) {
                ProductCategory::factory()
                    ->withProduct($product)
                    ->withCategory($category)
                    ->create();
            });
        });

        Product::join(
            'product_category',
            'products.id',
            '=',
            'product_category.product_id'
        )
            ->join(
                'categories',
                'product_category.category_id',
                '=',
                'categories.id'
            )
            ->where(
                'categories.id',
                '=',
                1
            )
            ->where(
                'products.producible_type',
                '=',
                'App\\Models\\InstagramPageProduct'
            )
            ->update([
                'products.price' => DB::raw('products.price + 1000')
            ]);


        dd(Product::all());
    }

    public function test_nine()
    {
        # Remove users who have not placed any orders in last 7 days and delete their associated orders.
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        User::factory()->create();
        User::join(
            'orders',
            'users.id',
            '=',
            'orders.user_id'
        )
            ->where(
                'orders.created_at',
                '>',
                DB::raw('DATE_SUB(NOW(), INTERVAL 7 DAY)')
            )
            ->delete();

        dd(User::all());
    }

    public function test_ten()
    {
        # Insert a User and an order with one page and two follower product.
        User::insert([
            'name' => 'sajjad',
            'email' => 'mohammadisajjad54@gmail.com',
            'password' => bcrypt(12345678),
        ]);

        InstagramFollowerProduct::insert([
            [
                'price_per_follower' => 10,
                'provider_name' => 'mohammad',
                'service_quality' => 10,
            ],
            [
                'price_per_follower' => 12,
                'provider_name' => 'sajjad',
                'service_quality' => 8,
            ],
        ]);

        InstagramPageProduct::insert([
            [
                'follower_count' => 10,
                'username' => 'mohammad',
                'following_count' => 10,
                'is_visible' => true,
                'posts_count' => 10,
            ],
        ]);

        Product::insert([
            [
                'price' => 10,
                'producible_id' => InstagramPageProduct::first()->id,
                'producible_type' => 'App\\InstagramPageProduct',
            ],
            [
                'price' => 12,
                'producible_id' => InstagramFollowerProduct::first()->id,
                'producible_type' => 'App\\InstagramFollowerProduct',
            ],
            [
                'price' => 14,
                'producible_id' => InstagramFollowerProduct::find(2)->id,
                'producible_type' => 'App\\InstagramFollowerProduct',
            ],
        ]);

        Order::insert([
            [
                'status' => 'completed',
                'user_id' => 1,
            ],
        ]);

        OrderItem::insert([
            [
                'product_id' => 1,
                'quantity' => 1,
                'price' => 10,
                'order_id' => 1,
            ],
            [
                'product_id' => 2,
                'quantity' => 1,
                'price' => 20,
                'order_id' => 1,
            ],
            [
                'product_id' => 3,
                'quantity' => 1,
                'price' => 30,
                'order_id' => 1,
            ],
        ]);
        dd(Order::get());
    }

    public function test_eleven()
    {
        // Increase the prices of products with high average ratings and decrease the
        // prices of products with low average ratings. if(less than 4) * 0.9
        // if(more than 8) * 1.1

        $products = Product::factory(10)->create();
        $products->each(function (Product $product) {
            Review::factory()->withReviewable($product)->create();
        });
        Review::factory(10)->withReviewable($products[0])->create();
        Product::query()
            ->update([
                'price' => DB::raw('
            CASE
                WHEN (
                    SELECT AVG(rate) FROM reviews
                    WHERE reviewable_id = products.id AND reviewable_type = "Product"
                ) > 8 THEN price * 1.1
                WHEN (
                    SELECT AVG(rate) FROM reviews
                    WHERE reviewable_id = products.id AND reviewable_type = "Product"
                ) < 4 THEN price * 0.9
                ELSE price
            END
        ')
            ]);
        dd(Product::get());
    }
}
