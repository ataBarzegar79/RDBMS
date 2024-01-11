<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
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
    }
}
