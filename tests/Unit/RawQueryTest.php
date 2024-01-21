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
            OrderItem::factory()->withOrder($order)->create();
        });
        $usersWithOrder = DB::select('
                        SELECT * FROM users
                        INNER JOIN orders
                        ON (users.id = orders.user_id)
                        WHERE (orders.status = ?)',['uncompleted']);
        dd($usersWithOrder);
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

        $productsWithCategory = DB::select('SELECT * FROM products
                                        LEFT JOIN (product_category,categories)
                                        ON (products.id = product_category.product_id
                                        AND product_category.category_id = categories.id)');
        dd($productsWithCategory);
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
        $categoriesWithNumberProducts = DB::select('
                                            SELECT categories.*,COUNT(product_id) AS Products FROM categories
                                            LEFT JOIN product_category
                                            ON categories.id = product_category.category_id
                                            GROUP BY categories.id');
        dd($categoriesWithNumberProducts);
    }
    public function test_four()
    {
        # Calculate the total revenue for each product type.
        $user = User::factory()->create();
        $orders = Order::factory()->withUser($user)->count(15)->create();
        $orders->each(function (Order $order) {
            OrderItem::factory()->withOrder($order)->create();
        });
        $totalRevenue = DB::select(
            "
            SELECT products.producible_type , SUM(order_items.quantity * order_items.price) AS revenue
            FROM products
            LEFT JOIN order_items on products.id = order_items.product_id
            LEFT JOIN orders on orders.id = order_items.order_id
            WHERE (orders.status = ? OR orders.status IS NULL )
            GROUP BY products.producible_type
            "
            , ['completed']);
        dd($totalRevenue);
    }
    public function test_five()
    {
        # Retrieve 5 products with the highest average ratings, considering reviews.
        $products = Product::factory(10)
            ->create();
        $products->each(function (Product $product) {
            Review::factory()
                ->withReviewable($product)
                ->create();
        });
        Review::factory(10)
            ->withReviewable($products[0])
            ->create();
        $topRateProducts = DB::select('
        SELECT products.*,
               AVG(rate) as Avgrate
        FROM products
        INNER JOIN reviews
        ON (products.id = reviews.reviewable_id)
        WHERE reviewable_type = ?
        GROUP BY products.id
        ORDER BY Avgrate DESC
        LIMIT 5
',[Product::class]);
        dd($topRateProducts);
    }
    public function test_six()
    {
        # Retrieve all reviews associated with a specific product type (has many through)
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
        $reviewsSpecificProductType = DB::select('
        SELECT *
        FROM reviews
        INNER JOIN products
        ON (reviews.reviewable_id = products.id
        AND reviews.reviewable_type = ?)
        WHERE producible_id = ? AND producible_type = ?

        ',[Product::class,2,InstagramPageProduct::class]);
        dd($reviewsSpecificProductType);
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
        $userOrderSpecificProduct = DB::select('
        SELECT * FROM users
        INNER JOIN (orders,order_items,products)
        ON (users.id = orders.user_id AND
        order_items.order_id = orders.id AND
        products.id = order_items.product_id)
        WHERE (order_items.product_id = ?
        AND orders.created_at >
            DATE_SUB(NOW(), INTERVAL 1 MONTH))
        ',[1]);
        // todo: do this with sub query

        dd($userOrderSpecificProduct);
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
        $updatePrice = DB::update('
        UPDATE products
        SET price = price + ?
        WHERE id IN (
            SELECT product_category.product_id
            FROM product_category
            INNER JOIN categories ON product_category.category_id = categories.id
            WHERE (categories.id = ? AND products.producible_type = ?)
        )
      ',[1000,1,InstagramPageProduct::class]);
        dd(Product::all());
    }
    public function test_nine()
    {
        # Remove users who have not placed any orders in last 7 days and delete their associated orders.
        $user = User::factory()->create();
        Order::factory()->withUser($user)->count(15)->create();
        User::factory()->create();
        DB::delete('
            DELETE users,orders
            FROM users
            INNER JOIN orders
            ON users.id = orders.user_id
            WHERE orders.created_at >
                   DATE_SUB(NOW(), INTERVAL 7 DAY)
   ');
        dd(User::all());
    }
    public function test_ten()
    {
        # Insert a User and an order with one page and two follower product.
        DB::insert('
        INSERT INTO users
        (name,email,password)
        VALUES
        ("sajjad","mohammadisajjad54@gmail.com",12345678)
');
        DB::insert('
        INSERT INTO instagram_follower_products
        (price_per_follower,provider_name,service_quality)
        VALUES
        (10,"mohammad",10),
        (12,"sajjad",8)
');
        DB::insert('
        INSERT INTO instagram_page_products
        (follower_count,username,following_count,is_visible,posts_count)
        VALUES
        (10,"mohammad",10,true,10)
');
        DB::insert('
        INSERT INTO products
        (price,producible_id,producible_type)
        VALUES
        (10,1,?),
        (12,1,?),
        (14,2,?)
',[
    InstagramPageProduct::class,
    InstagramFollowerProduct::class,
    InstagramFollowerProduct::class
        ]);
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
    public function test_eleven()
    {
        // Increase the prices of products with high average ratings and decrease the
        // prices of products with low average ratings. if(less than 4) * 0.9
        // if(more than 8) * 1.1
        $products = Product::factory(10)
            ->create();
        $products->each(function (Product $product) {
            Review::factory()
                ->withReviewable($product)
                ->create();
        });
        Review::factory(10)
            ->withReviewable($products[0])
            ->create();
        DB::update('
       UPDATE products
        SET price =
            CASE
                WHEN (
                    SELECT AVG(rate) FROM reviews
                        WHERE reviewable_id = products.id AND reviewable_type = ?
                        ) > 8 THEN price * 1.1
                WHEN (
                    SELECT AVG(rate) FROM reviews
                        WHERE reviewable_id = products.id AND reviewable_type = ?
                        ) < 4 THEN price * 0.9
                ELSE price
            END;

',[Product::class,Product::class]);
        dd(Product::all());
    }
}
