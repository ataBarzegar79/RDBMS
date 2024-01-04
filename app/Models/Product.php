<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    use HasFactory;

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class,'reviewable');
    }
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
}
