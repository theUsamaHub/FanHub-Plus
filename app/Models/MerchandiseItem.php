<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MerchandiseItem extends Model
{
    use LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image_media_id',
        'tag',
        'is_upcoming',
        'release_date',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_upcoming' => 'boolean',
            'release_date' => 'date',
            'view_count' => 'integer',
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
        return $this->imageMedia?->url ?: asset(config('homepage.images.merchandise'));
    }

    public function getDisplayTagAttribute(): ?string
    {
        return match ($this->tag) {
            'limited_edition' => 'Limited Edition',
            'pre_order' => 'Pre-Order',
            'collectible' => 'Collectible',
            default => $this->is_upcoming ? 'Coming Soon' : null,
        };
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_upcoming', true);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->where('tag', $tag);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (MerchandiseItem $item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->name);
            }
        });

        static::updating(function (MerchandiseItem $item) {
            if ($item->isDirty('name') && ! $item->slug) {
                $item->slug = Str::slug($item->name);
            }
        });
    }
}
