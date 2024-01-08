<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instagram_page_products', function (Blueprint $table) {
            $table->id();
            $table->integer('follower_count');
            $table->string('username')->unique();
            $table->integer('following_count');
            $table->boolean('is_visible');
            $table->string('bio')->nullable();
            $table->integer('posts_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_page_products');
    }
};
