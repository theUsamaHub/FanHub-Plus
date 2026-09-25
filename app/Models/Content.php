<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Content extends Model
{
    use LogsActivity;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'type',
        'excerpt',
        'body',
        'release_date',
        'release_label',
        'popularity_score',
        'view_count',
        'status',
        'is_featured',
        'is_user_submitted',
        'submitted_by',
        'reviewed_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'popularity_score' => 'decimal:2',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
            'is_user_submitted' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'content_media')
            ->withPivot(['role', 'sort_order'])
            ->orderBy('content_media.sort_order');
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(CharacterProfile::class, 'character_contents', 'content_id', 'character_id')->withTimestamps();
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tags')->withTimestamps();
    }

    public function getCoverAttribute(): ?Media
    {
        return $this->media->firstWhere('pivot.role', 'cover');
    }

    public function getArtworkUrlAttribute(): string
    {
        return $this->cover?->url ?? asset(config('homepage.artwork.'.($this->category?->slug ?? 'anime'), config('homepage.images.trending')));
    }

    public function mediaByRole(string $role)
    {
        return $this->media->where('pivot.role', $role)->values();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->published()->where(fn (Builder $query) => $query
            ->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function getReadingMinutesAttribute(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($this->body ?? $this->excerpt ?? '')), -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words ?: []) / 200));
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Content $content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
        });

        static::updating(function (Content $content) {
            if ($content->isDirty('title') && ! $content->slug) {
                $content->slug = Str::slug($content->title);
            }
        });
    }
}
