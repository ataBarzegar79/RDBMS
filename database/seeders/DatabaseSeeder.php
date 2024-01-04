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
        User::factory()->create();
        Category::factory(2)->create();
        Product::factory(4)->create();
        Review::factory(10)->create();
        Order::factory(5)->create();
    }
}
