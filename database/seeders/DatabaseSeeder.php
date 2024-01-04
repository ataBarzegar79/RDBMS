<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\InstagramFollower;
use App\Models\InstagramPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = Product::factory(5)->create();
        $categories = Category::factory(5)->create();

        $products->each(function ($product) {
            Review::factory(rand(1, 3))->create([
                'reviewable_id' => $product->id,
                'reviewable_type' => Product::class,
            ]);
        });

        $categories->each(function ($category) {
            Review::factory(rand(1, 3))->create([
                'reviewable_id' => $category->id,
                'reviewable_type' => Category::class,
            ]);
        });

        $orders = Order::factory(5)->create();

        $products->each(function ($product) use ($orders) {
            $product->orders()->attach(
                $orders->random(rand(1, 5))->pluck('id')->toArray()
            );
        });
    }
}
