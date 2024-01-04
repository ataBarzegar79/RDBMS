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
        Schema::create('page_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('username')->unique();
            $table->unsignedInteger('follower_count');
            $table->unsignedInteger('following_count');
            $table->text('bio')->nullable();
            $table->unsignedInteger('posts_count');
            $table->enum('visibility', ['Public', 'Private', 'Deactivated']);
            $table->timestamp('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_products');
    }
};
