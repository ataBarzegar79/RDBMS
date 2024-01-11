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
        Schema::create('instagram_followers', function (Blueprint $table) {
            $table->id();
            $table->integer('price_per_follower');
            $table->integer('provider_name');
            $table->integer('service_quality');
            $table->string('speed_of_follower_charging')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_followers');
    }
};
