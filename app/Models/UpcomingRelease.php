<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UpcomingRelease extends Model
{
    public const KINDS = ['anime', 'event', 'movie', 'series', 'game', 'merchandise'];

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'kind',
        'release_date',
        'release_label',
        'description',
        'image_media_id',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

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
        return $this->imageMedia?->url ?: asset(config('homepage.images.upcoming'));
    }

    public function getKindLabelAttribute(): string
    {
        return match ($this->kind) {
            'anime' => 'Anime',
            'event' => 'Event',
            'movie' => 'Movie',
            'series' => 'Series',
            'game' => 'Game',
            'merchandise' => 'Merchandise',
            default => ucfirst($this->kind),
        };
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('release_date')->orWhereDate('release_date', '>=', today()));
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (UpcomingRelease $release) {
            if (empty($release->slug)) {
                $release->slug = Str::slug($release->title);
            }
        });

        static::updating(function (UpcomingRelease $release) {
            if ($release->isDirty('title') && ! $release->slug) {
                $release->slug = Str::slug($release->title);
            }
        });
    }
}
