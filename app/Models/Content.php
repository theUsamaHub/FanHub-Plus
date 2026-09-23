<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Content extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'type',
        'excerpt',
        'body',
        'release_date',
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
            'popularity_score' => 'integer',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
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
