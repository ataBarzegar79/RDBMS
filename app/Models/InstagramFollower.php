<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstagramFollower extends Model
{
    use HasFactory, SoftDeletes;

    public function product()
    {
        return $this->morphOne(Product::class,'productable');
    }
}
