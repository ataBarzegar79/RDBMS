<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InstagramFollowerProduct extends Model
{
    use HasFactory;

    public function producible(): MorphOne
    {
        return $this->morphOne(Product::class, 'producible');
    }
}
