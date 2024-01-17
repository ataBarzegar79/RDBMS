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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    #[NoReturn] public function test_example(): void
    {
        # orders with specific user
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        dd($user->orders()->count());
    }

    #[NoReturn] public function test_one()
    {
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        $query = DB::select(
            "SELECT
                users.*,
                orders.status
             FROM
                orders
                INNER JOIN users ON orders.user_id = users.id
                AND status IN('uncompleted')
      ");
        dd($query);
    }

    #[NoReturn] public function test_two()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = DB::select("
            SELECT
                products.*,
                categories.*
            FROM
                products
                LEFT JOIN product_category ON product_category.product_id = products.id
                LEFT JOIN categories ON product_category.category_id = categories.id
        ");

        dd($query);
    }

    #[NoReturn] public function test_three()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = DB::select("
                 SELECT
                    categories.id,
                    categories.name,
                    COUNT(product_category.product_id) AS product_count
                FROM
                    categories
                    LEFT JOIN product_category ON product_category.category_id = categories.id
                GROUP BY
                    categories.id,
                    categories.name
        ");

        dd($query);
    }

    #[NoReturn] public function test_four()
    {

        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        OrderItem::factory(5)->withProduct($product)->create();

        $query = DB::select("
                SELECT
                    products.producible_id,
                     products.producible_type,
                    SUM(order_items.price * order_items.quantity) AS  revenue
                FROM
                    products
                    LEFT JOIN order_items ON products.id = order_items.product_id
                    LEFT JOIN orders ON orders.id = order_items.order_id
                WHERE
                    products.producible_type = ? AND orders.status = 'completed'
                GROUP BY
                    products.producible_id,
                     products.producible_type
        ",[$instagram::class]);
        dd($query);
    }

    #[NoReturn] public function test_five()
    {
        $product = Product::factory()->create();
        Review::factory()->withReviewable($product)->count(10)->create();
        $query = DB::select('
            SELECT
                products.id,
                products.price,
                reviews.reviewable_type,
                AVG(reviews.rate) AS avgs
            FROM
                products
            LEFT JOIN reviews ON products.id = reviews.reviewable_id
            WHERE
                reviews.reviewable_type = ?
            GROUP BY
                products.id,
                products.price,
                reviews.reviewable_type
            ORDER BY
                avgs DESC
            LIMIT
                5
        ',[$product::class]);

        dd($query);
    }

    #[NoReturn] public function test_six()
    {
        $product = Product::factory()->create();
        Review::factory()->withReviewable($product)->count(5)->create();

        $query = DB::select("
            SELECT
                products.*,
                reviews.*
            FROM
                reviews
                INNER JOIN products ON reviews.reviewable_id = products.id
                WHERE
                    reviews.reviewable_type = ?
        ",[$product::class]);

        dd($query);
    }

    #[NoReturn] public function test_seven()
    {
        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        OrderItem::factory(5)->withProduct($product)->create();

        $query = DB::select("
            SELECT
                users.*,
                producible_type
            FROM
                users
                INNER JOIN orders ON orders.user_id = users.id
                INNER JOIN order_items ON order_items.order_id = orders.id
                INNER JOIN products ON order_items.product_id = products.id
            WHERE
                products.producible_type = ?
                AND order_items.created_at > date_sub(now(), INTERVAL 1 MONTH)
        ",[$instagram::class]);

        dd($query);
    }

    #[NoReturn] public function test_eight()
    {
        $category = Category::factory()->create();
        $instagram = InstagramFollowerProduct::factory()->create();
        $product = Product::factory()->withProducible($instagram)->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        DB::update("
            UPDATE
                products
            SET
                products.price = products.price * 100
            WHERE
                id IN(
                    SELECT
                        products.id
                    FROM
                        product_category
                        INNER JOIN categories ON product_category.category_id = categories.id
                        INNER JOIN products ON product_category.product_id = products.id
                    WHERE
                        products.producible_type = ?
                )
        ",[$instagram::class]);

        $query = DB::select("
            SELECT
                *
            FROM
                products
        ");

        dd($query);
    }

    #[NoReturn] public function test_nine()
    {
        User::factory()->create();
        OrderItem::factory(5)->create();

        DB::delete("
            DELETE users,
                order_items,
                orders
            FROM
                users
                LEFT JOIN orders ON orders.user_id = users.id
                LEFT JOIN order_items ON orders.id = order_items.order_id
            WHERE
                orders.id IS NULL
                OR order_items.created_at < date_sub(now(), INTERVAL 7 DAY)
        ");

        $query = DB::select('
            SELECT
                *
            FROM
                users
        ');

        dd($query);
    }

    #[NoReturn] public function test_ten()
    {
        DB::insert('
                        INSERT INTO users
                            (name,email,password)
                        VALUES
                            ("ali","ali@gmail.com",123243284)
    ');
        DB::insert('
                        INSERT INTO instagram_follower_products
                            (price_per_follower,provider_name,service_quality)
                        VALUES
                            (11,"reza",11),
                            (2,"kazem",3)
    ');
        DB::insert('
                        INSERT INTO instagram_page_products
                            (follower_count,username,following_count,is_visible,posts_count)
                        VALUES
                            (5,"mohammad",8,true,10)
    ');
        DB::insert('
                        INSERT INTO products
                            (price,producible_id,producible_type)
                        VALUES
                            (10,1,"App\\InstagramPageProduct"),
                            (12,1,"App\\InstagramFollowerProduct")
    ');
        DB::insert('
                        INSERT INTO orders
                            (status,user_id)
                        VALUES
                            ("completed",1)
    ');
        DB::insert('
                        INSERT INTO order_items
                            (product_id,quantity,price,order_id)
                        VALUES
                            (1,1,10,1),
                            (2,1,20,1)
    ');
        dd(Order::all());
    }

    #[NoReturn] public function test_eleven()
    {
        $products = Product::factory()->create();
        Review::factory()->withReviewable($products)->count(5)->create();

        DB::select("
            UPDATE
                products
            SET
                products.price = CASE
                    WHEN (
                        SELECT
                            AVG(rate)
                        FROM
                            reviews
                        WHERE
                            reviewable_id = products.id
                            AND reviewable_type = ?
                    ) > 8 THEN price * 1.1
                    WHEN (
                        SELECT
                            AVG(rate)
                        FROM
                            reviews
                        WHERE
                            reviewable_id = products.id
                            AND reviewable_type = ?
                    ) < 4 THEN price * 0.9
                    ELSE price
                END;
        ",[$products::class,$products::class]);

        $query = DB::select("
            SELECT
                *
            FROM products
        ");

        dd($query);
    }
}
