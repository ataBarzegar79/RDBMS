<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InstagramPageProduct extends Model
{
    use HasFactory;

    public function producible(): MorphOne
    {
        return $this->morphOne(Product::class, 'producible');
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            Product::class,
            'producible_id',
            'reviewable_id',
            'id',
            'id'
        )->where('producible_type', InstagramPageProduct::class);
    }
}
