<?php


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
use JetBrains\PhpStorm\NoReturn;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class RawQueryAnswersTest extends TestCase
{
    use RefreshDatabase;

    #[NoReturn] public function testOne()
    {
        #q1
        // Retrieve user details along with the uncompleted orders they have placed.
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        User::factory()->create();
        Order::factory()->withUser($userA)->count(15)->create();
        Order::factory()->withUser($userB)->count(10)->create();
        $result = DB::select("SELECT users.id, users.name, orders.status, orders.created_at
                                    FROM users
                                    INNER JOIN orders ON users.id = orders.user_id
                                    WHERE  orders.status = ?
                                    ", ['uncompleted']);
        dd($result);

    }

    #[NoReturn] public function testTwo()
    {
        #q2
        // Display all products and their associated categories. Include products without categories.
        $productA = Product::factory()->create();
        Category::factory(15)->create()->each(function (Category $category) use ($productA) {
            ProductCategory::factory()->withIds($productA->id, $category->id)->create();
        });
        Product::factory()->create();
        $result = DB::select("SELECT products.*
                                    FROM products
                                    LEFT JOIN product_category ON products.id = product_category.product_id
                                    WHERE product_category.category_id IS NULL;");
        dd($result);
    }

    #[NoReturn] public function testThree()
    {
        #q3
        // Display all categories and the number of products in each category.
        $productA = Product::factory()->create();
        Product::factory()->create();

        $category = Category::factory()->create();
        Category::factory(15)->create()->each(function (Category $category) use ($productA) {
            ProductCategory::factory()->withIds($productA->id, $category->id)->create();
        });

        Product::factory(30)->create()->each(function (Product $product) use ($category) {
            ProductCategory::factory()->withIds($product->id, $category->id)->create();
        });
        $result = DB::select("SELECT categories.*, COUNT(product_category.product_id) AS products_count
                                    FROM categories
                                    LEFT JOIN product_category ON categories.id = product_category.category_id
                                    GROUP BY categories.id");
        dd($result);
    }

    #[NoReturn] public function testFour()
    {
        #q4
        // Calculate the total revenue for each product type.
        Product::factory(5)
            ->create()
            ->each(function (Product $product) {
                OrderItem::factory()->withProduct($product)->count(rand(10, 30))->create();
            });
        Product::factory()->create();
        $result = DB::select(
            "
            SELECT products.producible_type , SUM(order_items.quantity * order_items.price) AS revenue
            FROM products
            LEFT JOIN order_items on products.id = order_items.product_id
            LEFT JOIN orders on orders.id = order_items.order_id
            WHERE (orders.status = ? OR orders.status IS NULL )
            GROUP BY products.producible_type
            "
            , ['completed']);

        dd($result);
    }

    #[NoReturn] public function testFive()
    {
        #q5
        // Retrieve 5 products with the highest average ratings, considering reviews.
        Category::factory(3)->create()->each(function ($category) {
            $products = Product::factory(20)->create();
            // Attach products to the category using the pivot table
            $category->products()->attach($products);

            // Create some reviews for each product
            $products->each(function ($product) {
                Review::factory(count: rand(5, 10))->create([
                    'reviewable_id' => $product->id,
                    'reviewable_type' => Product::class,
                ]);
            });
        });
        $result = DB::select("SELECT
                                    products.id AS product_id,
                                    products.price AS product_price,
                                    AVG(reviews.rate) AS average_rating
                                FROM products
                                LEFT JOIN reviews ON products.id = reviews.reviewable_id
                                                           AND reviews.reviewable_type = ?
                                GROUP BY products.id, products.price
                                ORDER BY average_rating DESC
                                ", [Product::class]);
        dd($result);
    }

    #[NoReturn] public function testSix()
    {
        #q6
        // Retrieve all reviews associated with a specific product type (has many through)
        InstagramPageProduct::factory(3)->create()->each(function ($pageProduct) {
            $product = Product::factory()->create();
            $pageProduct->producible()->save($product);
        });
        $products = Product::all();
        $products->each(function ($product) {
            Review::factory(count: rand(5, 10))->create([
                'reviewable_id' => $product->id,
                'reviewable_type' => Product::class,
            ]);
        });
        $result = DB::select('SELECT *
                                    FROM reviews
                                    INNER JOIN products ON products.id = reviews.reviewable_id
                                    WHERE products.producible_id = ? AND producible_type = ?
                                    ', [2, 'App\Models\InstagramPageProduct']);
        dd($result);
    }

    #[NoReturn] public function testSeven()
    {
        #q7
        // Retrieve users who have ordered a specific product during the last month.
        $product = Product::factory()->create();
        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($product) {
                $orders = Order::factory()->withUser($user)
                    ->withCreatedAt(now()->subDays(rand(1, 29)))
                    ->count(rand(1, 6))
                    ->create();
                OrderItem::factory()
                    ->withOrder($orders->random())
                    ->withProduct($product)
                    ->count(rand(1, 6))
                    ->create();
            });

        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($product) {
                $orders = Order::factory()->withUser($user)
                    ->withCreatedAt(now()->subDays(rand(31, 90)))
                    ->count(rand(1, 6))
                    ->create();
                OrderItem::factory()
                    ->withOrder($orders->random())
                    ->withProduct($product)
                    ->count(rand(1, 6))
                    ->create();
            });
        $oneMonthAgo = now()->subMonth();
        $resultWithQuery = DB::select(
            "
                   SELECT DISTINCT users.*
                   FROM users
                   INNER JOIN orders ON orders.user_id = users.id
                   INNER JOIN order_items ON orders.id = order_items.order_id
                   WHERE orders.created_at > ?
                   AND order_items.product_id = ?
                   "
            , [$oneMonthAgo, 1]);

        $result = DB::select
        (
            "
                    SELECT DISTINCT users.*
                    FROM users
                    WHERE users.id IN (
                        SELECT orders.user_id
                        FROM orders
                        WHERE orders.id IN (
                            SELECT order_items.order_id
                            FROM order_items
                            WHERE order_items.product_id = ?
                        )
                        AND orders.created_at >= ?)
                        "
            , [1, $oneMonthAgo]);
        dd($result, $resultWithQuery);
    }

    #[NoReturn] public function testEight()
    {
        #q8
        // Increase the prices of all products in a specific category and with a certain product type.
        $productClass = InstagramPageProduct::class;
        $category = Category::factory()->create();
        Product::factory()->count(5)
            ->withProductType($productClass)
            ->create()
            ->each(
                function (Product $product) use ($category) {
                    $product->categories()->attach($category);
                }
            );
        Product::factory()->count(2)->create();
        $productsBeforeUpdate = Product::pluck('price')->all();
        DB::update(
            "
           UPDATE products
           SET price = price + (price * 5/100)
            WHERE id IN(
                SELECT product_id
                FROM product_category
                WHERE category_id = ?
            )
            AND producible_type = ?
            ", [1, $productClass]);

        dd($productsBeforeUpdate, Product::pluck('price'));
    }

    #[NoReturn] public function testNine()
    {
        #q9
        // Remove users who have not placed any orders in last 7 days and delete their associated orders.
        User::factory()->count(5)->create()->each(function (User $user) {
            Order::factory()->count(rand(4, 10))->withUser($user)->withCreatedAt(now()->subDays(rand(8, 100)))->create();
        });

        Order::factory()->withUser(User::first())->withCreatedAt(now()->subDays())->create();
        $beforeDeleteCount = Order::count();
        $targetUserOrderCount = User::first()->orders()->count();
        DB::delete(
            "
            DELETE FROM users
            WHERE id NOT IN (
                SELECT DISTINCT users.id
                FROM users
                LEFT JOIN orders ON users.id = orders.user_id
                WHERE  orders.created_at > ?
            );
            "
            , [now()->subDays(7)]);

        dd(Order::count(), $beforeDeleteCount, $targetUserOrderCount);
    }

    #[NoReturn] public function testTen()
    {
        #q10
        // Insert a User and an order with one page and two follower product.
        DB::insert('INSERT INTO users (name, email, password, created_at, updated_at)
                            VALUES (:name, :email, :password, :created_at, :updated_at);
                            ', ['Ehsan', 'ehsan@gamil.com', bcrypt('password'), now(), now()]);

        DB::insert('INSERT INTO orders(user_id, status, created_at, updated_at)
                            VALUES(:user_id, :status, :created_at, :updated_at);
                            ', [1, 'uncompleted', now(), now()]);

        DB::insert('INSERT INTO instagram_page_products(
                                    username,
                                    follower_count,
                                    following_count,
                                    is_visible,
                                    posts_count,
                                    bio,
                                    created_at,
                                    updated_at
                                    )
                            VALUES(:username, :follower_count, :following_count, :is_visible, :posts_count, :bio, :created_at, :updated_at);
                            ', ['ehsan_inbo', 8500, 45, 1, 19, 'bio here', now(), now()]);
        DB::insert('INSERT INTO instagram_follower_products(provider_name, price_per_follower, service_quality, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?),
                                   (?, ?, ?, ?, ?);
                            ', [
            'providerOne', 10.9, 1, now(), now(),
            'providerTwo', 6.9, 0, now(), now()
        ]);

        DB::insert('INSERT INTO products(price, quantity, is_active, producible_id, producible_type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?),
                   (?, ?, ?, ?, ?, ?, ?),
                   (?, ?, ?, ?, ?, ?, ?);
            ', [
            10, 5, true, 1, Product::class, now(), now(),
            10, 5, true, 1, InstagramPageProduct::class, now(), now(),
            10, 5, true, 2, InstagramFollowerProduct::class, now(), now()
        ]);

        DB::insert('INSERT INTO order_items(order_id, product_id, quantity, price, created_at, updated_at)
                            VALUES(?, ?, ?, ?, ?, ?),
                                  (?, ?, ?, ?, ?, ?),
                                  (?, ?, ?, ?, ?, ?);
                            ', [
            1, 1, 1, 1.99, now(), now(),
            1, 2, 2, 0.99, now(), now(),
            1, 3, 1, 2.99, now(), now()
        ]);

        dd(json_decode(Order::with('orderItems')->get()));
    }

    #[NoReturn] public function testEleven()
    {
        #q11
        // Increase the prices of products with high average ratings and decrease the prices of products with low
        // average ratings. if(less than 4) * 0.9 if(more than 8) * 1.1
        Product::factory()->count(2)->create()->each(function (Product $product) {
            Review::factory()
                ->withRate(9)
                ->withProducible($product->id, Product::class)
                ->count(20)
                ->create();
        });

        Product::factory()->count(3)->create()->each(function (Product $product) {
            Review::factory()
                ->withRate(3)
                ->withProducible($product->id, Product::class)
                ->count(20)
                ->create();
        });

        $pricesBeforeUpdate = Product::pluck('price');
        DB::update(
            '
            UPDATE products
            SET price = price * 9
            WHERE (
                        SELECT AVG(reviews.rate)
                        FROM reviews
                        WHERE reviews.reviewable_id = products.id AND reviews.reviewable_type = ?
            ) < 4
    ', [Product::class]);

        DB::update(
            '
            UPDATE products
            SET price = price * 1.1
            WHERE (
                        SELECT AVG(reviews.rate)
                        FROM reviews
                        WHERE reviews.reviewable_id = products.id AND reviews.reviewable_type = ?
            ) > 8
    ', [Product::class]);
        dd($pricesBeforeUpdate, Product::pluck('price'));
    }
}
