<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CharacterProfile extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'bio',
        'image_media_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (CharacterProfile $profile) {
            if (empty($profile->slug)) {
                $profile->slug = Str::slug($profile->name);
            }
        });

        static::updating(function (CharacterProfile $profile) {
            if ($profile->isDirty('name') && ! $profile->slug) {
                $profile->slug = Str::slug($profile->name);
            }
        });
    }
}
