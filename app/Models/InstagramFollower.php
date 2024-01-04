<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InstagramFollower extends Model
{
    use HasFactory;

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }
}
