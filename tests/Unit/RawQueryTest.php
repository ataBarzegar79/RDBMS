<?php

namespace Tests\Unit;

use App\Models\Category;
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
            "SELECT users.*,orders.status
                   FROM orders
                   INNER JOIN users ON orders.user_id = users.id AND status IN('uncompleted')
                   "
        );
        dd($query);
    }

    #[NoReturn] public function test_two()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = DB::select("
            SELECT products.*,categories.*
            FROM product_category
            JOIN products
            LEFT JOIN categories ON product_category.product_id = products.id
            AND product_category.category_id = categories.id
        ");

        dd($query);
    }

    #[NoReturn] public function test_three()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = DB::select("
                  SELECT categories.id,categories.name ,COUNT(product_category.product_id) AS product_count
                  FROM product_category
                  INNER JOIN (products,categories) ON product_category.category_id = categories.id
                  AND product_category.product_id = products.id
                  GROUP BY categories.id,categories.name
        ");

        dd($query);
    }

    #[NoReturn] public function test_four()
    {
        OrderItem::factory(5)->create();

        $query = DB::select('
        SELECT order_items.id,order_items.price,order_items.quantity,order_items.price*order_items.quantity
        AS multpy
        FROM order_items
        GROUP BY order_items.id,order_items.price,order_items.quantity
        ');
        dd($query);
    }

    #[NoReturn] public function test_five()
    {
        Review::factory(10)->create();
        $query = DB::select('
            SELECT reviews.id,reviews.content,reviews.rate,reviews.reviewable_type,reviews.reviewable_id,
            AVG(reviews.rate)
            AS avgs
            FROM reviews
            GROUP BY reviews.id,reviews.content,reviews.rate,
            reviews.reviewable_type,reviews.reviewable_id
            ORDER BY avgs
            DESC LIMIT 5
        ');

        dd($query);
    }

    #[NoReturn] public function test_six()
    {
        $product = Product::factory()->create();
        Review::factory()->withReviewable($product)->count(5)->create();

        $query = DB::select("
            SELECT products.* , reviews.*
            FROM products
            LEFT JOIN reviews
            ON reviews.reviewable_type = 'product'
            AND reviews.reviewable_id = products.id
        ");

        dd($query);
    }

    #[NoReturn] public function test_seven()
    {
        OrderItem::factory(5)->create();

        $query = DB::select("
            SELECT users.*,producible_type
            FROM order_items
            INNER JOIN (products,orders,users)
            ON  products.producible_type = 'InstagramPage'
            AND order_items.product_id = products.id
            AND order_items.order_id = orders.id
            AND orders.user_id = users.id
        ");

        dd($query);
    }

    #[NoReturn] public function test_eight()
    {
        $category = Category::factory()->create([
            'name' => 'child'
        ]);

        $product = Product::factory()->create([
            'producible_type' => 'instagramPage'
        ]);

        ProductCategory::factory()->withProduct($product)->withCategory($category)->count(5)->create();

        $query = DB::update("
            UPDATE products
            SET products.price = products.price * 100
            WHERE id
            IN(SELECT products.id
               FROM product_category
                   INNER JOIN (products,categories)
                       ON product_category.category_id = categories.id
                        AND product_category.product_id = products.id
                        AND products.producible_type = 'instagramPage'
                        AND categories.name = 'child')
        ");

        $query1 = DB::select("
            SELECT * FROM products
        ");

        dd($query1);
    }

    #[NoReturn] public function test_nine()
    {
        User::factory()->create();
        OrderItem::factory(5)->create();

        $query = DB::delete("
            DELETE users,order_items,orders
            FROM users
            LEFT JOIN (order_items, orders)
                   ON (
                orders.id = order_items.order_id
                AND orders.user_id = users.id
            )
             WHERE orders.id IS NULL OR order_items.created_at < date_sub(now(),INTERVAL 7 DAY)
        ");

        $query1 = DB::select('
            SELECT * FROM users
        ');

        dd($query1);
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
                        (12,1,"App\\InstagramFollowerProduct"),
                        (14,2,"App\\InstagramFollowerProduct")
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
                        (2,1,20,1),
                        (3,1,30,1)
    ');
        dd(Order::all());
    }

    #[NoReturn] public function test_eleven()
    {
        $products = Product::factory()->create();
        Review::factory()->withReviewable($products)->count(5)->create();

        $query = DB::select("
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
                            AND reviewable_type = 'product'
                    ) > 8 THEN price * 1.1
                    WHEN (
                        SELECT
                            AVG(rate)
                        FROM
                            reviews
                        WHERE
                            reviewable_id = products.id
                            AND reviewable_type = 'product'
                    ) < 4 THEN price * 0.9
                    ELSE price
                END;
        ");

        $query1 = DB::select("
            SELECT * FROM products
        ");

        dd($query1);
    }
}
