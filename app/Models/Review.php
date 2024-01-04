<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    use HasFactory;
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}
