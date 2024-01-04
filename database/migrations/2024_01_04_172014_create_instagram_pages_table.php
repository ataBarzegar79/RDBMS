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
        Schema::create('instagram_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('followers_count');
            $table->integer('following_count');
            $table->integer('post_count');
            $table->integer('visibility');
            $table->string('username');
            $table->string('bio');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_pages');
    }
};
