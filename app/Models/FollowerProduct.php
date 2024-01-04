<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowerProduct extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->morphOne(Product::class, 'producible');
    }
}
