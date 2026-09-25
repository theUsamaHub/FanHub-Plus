<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CharacterProfile extends Model
{
    use LogsActivity;

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

    public function getArtworkUrlAttribute(): string
    {
        return $this->imageMedia?->isImage()
            ? $this->imageMedia->url
            : asset(config('homepage.images.character'));
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'character_contents', 'character_id', 'content_id')->withTimestamps();
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
