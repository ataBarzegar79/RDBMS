<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\InstagramPageProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
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

    public function test_one(): void
    {
        // Retrieve user details along with the uncompleted orders they have placed.
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        $db = DB::select("SELECT
                                users.id, users.name, orders.id, orders.status
                                FROM users
                                INNER JOIN orders ON orders.user_id = users.id
                                WHERE orders.status = 'uncompleted'
                                "
        );
        dd($db);
    }

    public function test_two(): void
    {
        // Display all products and their associated categories. Include products without categories.
        $product = Product::factory(15)->create();
        $category = Category::factory(2)->create();

        // this will create 16 ProductCategory with the same product and category - INCORRECT
        # ProductCategory::factory(16)->withProductCategory($product->random(), $category->random())->create();

        // THE CORRECT & RIGHT WAY:
        for ($i = 0; $i < 16; $i++)
            ProductCategory::factory()->withProductCategory($product->random(), $category->random())->create();

        $db = DB::select("SELECT
                                product_category.id, products.id, products.price, products.quantity, categories.name, categories.id
                                FROM product_category INNER JOIN products
                                ON product_category.product_id = products.id
                                LEFT JOIN categories
                                ON product_category.category_id = categories.id
        ");
        dd($db);
    }

    public function test_three(): void
    {
        // Display all categories and the number of products in each category.
        $category = Category::factory(2)->create();
        $product = Product::factory(100)->create();

        for ($i = 0; $i < 100; $i++)
            ProductCategory::factory()->withProductCategory($product->random(), $category->random())->create();

        $db = DB::select("SELECT
                                categories.id, categories.name,  COUNT(product_category.product_id)
                                FROM product_category LEFT JOIN categories
                                ON product_category.category_id = categories.id
                                GROUP BY categories.id, categories.name
                                ");
        dd($db);
    }

    public function test_four(): void
    {
        // Calculate the total revenue for each product type.
        $product = Product::factory(500)->create();
        $user = User::factory(15)->create();

        for ($i = 0; $i < 50; $i++)
        {
            $order = Order::factory(15)->withUser($user->random())->create();
            OrderItem::factory(5)->withOrder($order->random())->withProduct($product->random())->create();
        }

        $db = DB::select("SELECT
                                products.producible_type, SUM(order_items.quantity * order_items.price) AS total_revenue
                                FROM products
                                INNER JOIN order_items ON products.id = order_items.product_id
                                GROUP BY products.producible_type
                                ");
        dd($db);
    }

    public function test_five():void
    {
        // Retrieve 5 products with the highest average ratings, considering reviews.
        Product::factory(500)->create();
        $user = User::factory(100)->create();

        for ($i = 0; $i < 15; $i++)
            Review::factory(10)->withUser($user->random())->withReviewable_type("product")->create();

        $db = DB::select("SELECT
                                products.id, products.price, products.quantity, AVG(reviews.rate) AS average_rating
                                FROM products
                                INNER JOIN reviews
                                ON products.id = reviews.reviewable_id
                                AND reviews.reviewable_type = 'App\\Models\\Product'
                                GROUP BY products.id, products.price, products.quantity
                                ORDER BY average_rating DESC
                                LIMIT 5
                                ");
        dd($db);
    }

    public function test_six(): void
    {

        // Retrieve all reviews associated with a specific product type
        $product = Product::factory()->withProducible_type("App\\Models\\InstagramPageProduct")->create();
        Review::factory (200)->create
        ([
            'reviewable_type' => 'App\\Models\\Product',
            'reviewable_id' => $product
        ]);

        $db = DB::select("SELECT
                                reviews.rate, reviews.content, products.producible_type
                                FROM reviews INNER JOIN products
                                ON reviews.reviewable_id = products.id
                                WHERE reviews.reviewable_type = 'App\\Models\\Product'
                                AND products.producible_type = 'App\\Models\\InstagramPageProduct'
                                ");
        dd($db);

    }

    public function test_seven(): void
    {
        // Retrieve users who have ordered a specific product during the last month.
        $user = User::factory(100)->create();
        for ($i = 0; $i < 100; $i++)
        {
            $order = Order::factory(15)->withUser($user->random())->create();
            $product = Product::factory(5)->create();
            OrderItem::factory(15)->withOrder($order->random())->withProduct($product->random());
        }

        $db = DB::select("SELECT
                                users.id, users.name, products.producible_type
                                FROM order_items INNER JOIN products
                                ON order_items.product_id = products.id
                                INNER JOIN orders
                                ON orders.id = order_items.order_id
                                INNER JOIN users
                                ON orders.user_id = users.id
                                WHERE products.producible_type = 'App\\Models\\InstagramPageProducts'
                                AND order_items.created_at >= CURDATE() - INTERVAL 1 MONTH
                                ");
        dd($db);
    }

    public function test_eight(): void
    {
        // Increase the prices of all products in a specific category and with a certain product type. (example: by 2 times)
        $product = Product::factory(100)->withProducible_type("App\\Models\\InstagramPageProduct")->create();
        $categories = Category::factory()->create();
        for ($i = 0; $i < 50; $i++)
            ProductCategory::factory()->withProductCategory($product->random(), $categories)->create();

        $db = DB::update("UPDATE products
                                INNER JOIN product_category
                                ON productS.id = product_category.product_id
                                SET products.price = products.price * 2
                                WHERE product_category.category_id = 2
                                AND products.producible_type = 'App\\Models\\InstagramPageProduct'
                                ");
        dd($db);
    }


    public function test_nine(): void
    {
        // Remove users who have not placed any orders in last 7 days and delete their associated orders.
        $user = User::factory(100)->create();
        for ($i = 0; $i < 30; $i++)
            Order::factory()->withUser($user->random())->create();

        $db = DB::delete("DELETE users, orders
                                FROM users
                                LEFT JOIN orders
                                ON users.id = orders.user_id
                                WHERE orders.created_at IS NULL
                                OR orders.created_at < DATE(NOW() - INTERVAL 7 DAY)
                                ");
        dd($db);
    }

    public function test_ten(): void
    {
        // Insert a User and an order with one page and two follower product.
        $insert_user_db = DB::insert("INSERT INTO users (id, name, email, password)
                                     VALUES (1, 'inbo', 'inboteam@inbo.ir', 'inbo123')");

        $insert_order_db = DB::insert("INSERT INTO orders (id, status, user_id)
                                       VALUES (1, 'uncompleted', 1)");

        DB::insert("INSERT INTO products (id, price, quantity, is_active, producible_type, producible_id)
                                                VALUES (1, 290, 1, 1, 'App\\Models\\InstagramPageProduct', 1)");

        DB::insert("INSERT INTO products (id, price, quantity, is_active, producible_type, producible_id)
                                                VALUES (2, 100, 2, 1, 'App\\Models\\InstagramFollowerProduct', 1)");

        $insert_order_items_db = DB::insert("INSERT INTO order_items (id, product_id, quantity, price, order_id)
                                                   VALUES (1, 1, 1, 290, 1)");

        $insert_order_item_two_db = DB::insert("INSERT INTO order_items (id, product_id, quantity, price, order_id)
                                                   VALUES (2, 2, 2, 200, 1)");

        var_dump($insert_user_db);
        var_dump($insert_order_db);
        var_dump($insert_order_items_db);
        var_dump($insert_order_item_two_db);

    }


    public function test_eleven(): void
    {
        $user = User::factory(100)->create();
        for ($i = 0; $i < 15; $i++)
            Review::factory(15)->withUser($user->random())->withReviewable_type("App\Models\Product")->create();

        Product::factory(500)->create();


        // Increase the prices of products with high average ratings and decrease the prices of products with low average ratings.
        $db = DB::update("UPDATE products
                                JOIN ( SELECT reviews.reviewable_id, AVG(reviews.rate) AS avg_rating
                                       FROM reviews
                                       WHERE reviews.reviewable_type = 'App\\Models\\Product'
                                       GROUP BY reviews.reviewable_id )
                                reviews
                                ON products.id = reviews.reviewable_id
                                SET products.price =
                                CASE
                                WHEN reviews.avg_rating < 4 THEN products.price * 0.9
                                WHEN reviews.avg_rating > 8 THEN products.price * 1.1
                                END
                                ");
        dd($db);
    }

}
