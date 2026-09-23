<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'rateable_type',
        'rateable_id',
        'rating_type',
        'stars',
        'is_thumbs_up',
    ];

    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'is_thumbs_up' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTargetLabelAttribute(): string
    {
        return Review::friendlyTargetLabel($this->rateable_type, $this->rateable_id, $this->rateable);
    }
}
