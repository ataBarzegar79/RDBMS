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
            OrderItem::factory()->create(['order_id' => $order->id]);
        });
        $usersWithOrder = DB::select('
                        SELECT * FROM users
                        INNER JOIN orders
                        ON (users.id = orders.user_id)
                        WHERE (orders.status = "uncompleted")');
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

        $productsWithCategory = DB::select('SELECT * FROM products
                                        LEFT OUTER JOIN (product_category,categories)
                                        ON (products.id = product_category.product_id
                                        AND product_category.category_id = categories.id)');
        dd($productsWithCategory);
    }
    public function test_three()
    {
        # Display all categories and the number of products in each category.
        $products = Product::factory(20)->create();
        Category::factory(10)->create();
        $products->each(function (Product $product) {
            ProductCategory::factory()->create([
                'category_id' => 1,
                'product_id' => $product->id,
            ]);
        });
        $newProducts = Product::factory(20)->create();
        $newProducts->each(function (Product $product) {
            ProductCategory::factory()->create([
                'category_id' => 2,
                'product_id' => $product->id,
            ]);
        });
        Category::factory(20)->create();
        $categoriesWithNumberProducts = DB::select('
                                            SELECT name,categories.id,COUNT(category_id) AS Products FROM categories
                                            LEFT OUTER JOIN product_category
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
            OrderItem::factory()->create(['order_id' => $order->id]);
        });
        $totalRevenue = DB::select('
                                    SELECT *,price * quantity AS totalSell FROM order_items
                                    INNER JOIN orders
                                    ON order_items.order_id = orders.id
                                    WHERE status = "completed"
                                    ');
        dd($totalRevenue);
    }
    public function test_five()
    {
        # Retrieve 5 products with the highest average ratings, considering reviews.
        $products = Product::factory(10)->create();
        $products->each(function (Product $product){
            Review::factory()->create([
                'reviewable_id' => $product->id,
                'reviewable_type' => 'Product'
            ]);
        });
        Review::factory(10)->create([
            'reviewable_id' => 1,
            'reviewable_type' => 'Product'
        ]);
        $topRateProducts = DB::select('
                                    SELECT products.*,AVG(rate) as Avgrate FROM products
                                    INNER JOIN reviews
                                    ON (products.id = reviews.reviewable_id)
                                    WHERE reviewable_type = "Product"
                                    GROUP BY products.id
                                    ORDER BY Avgrate DESC
                                    LIMIT 5
');
        dd($topRateProducts);
    }
    public function test_six()
    {
        # Retrieve all reviews associated with a specific product type (has many through)
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(function (InstagramFollowerProduct $instagramFollowerProduct){
            Product::factory()->create([
                'producible_id' => $instagramFollowerProduct->id,
                'producible_type' => 'InstagramFollowerProduct'
            ]);
        });
        $instagramPage->each(function (InstagramPageProduct $instagramPageProduct){
            Product::factory()->create([
                'producible_id' => $instagramPageProduct->id,
                'producible_type' => 'InstagramPageProduct'
            ]);
        });
        $products = Product::all();
        $products->each(function (Product $product){
            Review::factory()->create([
                'reviewable_id' => $product->id,
                'reviewable_type' => 'Product'
            ]);
        });
        Review::factory(10)->create([
            'reviewable_id' => 1,
            'reviewable_type' => 'Product'
        ]);
        $reviewsSpecificProductType = DB::select('
                                            SELECT * FROM reviews
                                            INNER JOIN products
                                            ON
                                                (reviews.reviewable_id = products.id
                                                AND reviews.reviewable_type = "Product")
                                            WHERE producible_type = ?

        ',['InstagramPageProduct']);
        dd($reviewsSpecificProductType);
    }
    public function test_seven()
    {
        # Retrieve users who have ordered a specific product during the last month.
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(function (InstagramFollowerProduct $instagramFollowerProduct){
            Product::factory()->create([
                'producible_id' => $instagramFollowerProduct->id,
                'producible_type' => 'InstagramFollowerProduct'
            ]);
        });
        $instagramPage->each(function (InstagramPageProduct $instagramPageProduct){
            Product::factory()->create([
                'producible_id' => $instagramPageProduct->id,
                'producible_type' => 'InstagramPageProduct'
            ]);
        });
        $user = User::factory()->create();
        $orders = Order::factory()->withUser($user)->count(15)->create();
        $orders->each(function (Order $order) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $order->id
            ]);
        });
        $userOrderSpecificProduct = DB::select('
                                            SELECT * FROM users
                                            INNER JOIN (orders,order_items,products)
                                            ON (users.id = orders.user_id AND
                                            order_items.order_id = orders.id AND
                                            products.id = order_items.product_id)
                                            WHERE (producible_type = ? AND orders.created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH))
        ',['InstagramPageProduct']);
        // todo: do this with sub query

        dd($userOrderSpecificProduct);
    }
    public function test_eight()
    {
        # Increase the prices of all products in a specific category and with a certain product type.
        $instagramPage = InstagramPageProduct::factory(10)->create();
        $instagramFollower = InstagramFollowerProduct::factory(10)->create();
        $instagramFollower->each(function (InstagramFollowerProduct $instagramFollowerProduct){
            Product::factory()->create([
                'producible_id' => $instagramFollowerProduct->id,
                'producible_type' => 'InstagramFollowerProduct'
            ]);
        });
        $instagramPage->each(function (InstagramPageProduct $instagramPageProduct){
            Product::factory()->create([
                'producible_id' => $instagramPageProduct->id,
                'producible_type' => 'InstagramPageProduct'
            ]);
        });
        $products = Product::all();
        Category::factory(10)->create();
        $products->each(function (Product $product) {
            ProductCategory::factory()->create([
                'category_id' => 1,
                'product_id' => $product->id,
            ]);
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
                                                                          ',[1000,1,'InstagramPageProduct']);
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
                    WHERE orders.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
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
    public function test_eleven()
    {
        // Increase the prices of products with high average ratings and decrease the
        // prices of products with low average ratings. if(less than 4) * 0.9
        // if(more than 8) * 1.1

        $products = Product::factory(10)->create();
        $products->each(function (Product $product){
            Review::factory()->create([
                'reviewable_id' => $product->id,
                'reviewable_type' => 'Product'
            ]);
        });
        Review::factory(10)->create([
            'reviewable_id' => 1,
            'reviewable_type' => 'Product'
        ]);
        DB::update('
                                   UPDATE products
                                    SET price =
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
                                        END;

');
        dd(Product::all());
    }
}
