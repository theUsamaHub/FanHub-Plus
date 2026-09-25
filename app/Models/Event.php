<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use LogsActivity;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'city',
        'venue',
        'address',
        'latitude',
        'longitude',
        'start_at',
        'end_at',
        'ticket_url',
        'cover_media_id',
        'status',
        'slug', 'event_type', 'short_description', 'is_featured', 'popularity_score', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_featured' => 'boolean',
            'popularity_score' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function galleryMedia(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'event_media')->orderBy('media.id');
    }

    public function getFallbackArtworkAttribute(): string
    {
        return asset(config('homepage.artwork.'.$this->category?->slug, config('events.fallback')));
    }

    public function getArtworkUrlAttribute(): string
    {
        return $this->coverMedia?->isImage() ? $this->coverMedia->url : $this->fallback_artwork;
    }

    public function getSummaryAttribute(): string
    {
        return $this->short_description ?: Str::limit(strip_tags($this->description ?? ''), 180);
    }

    public function getTypeLabelAttribute(): ?string
    {
        return config('events.types.'.$this->event_type);
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'cancelled') return 'Cancelled';
        if ($this->start_at->isFuture()) return 'Upcoming';
        if ($this->end_at?->isFuture()) return 'Happening now';
        return $this->end_at ? 'Ended' : 'Started';
    }

    public function getSafeTicketUrlAttribute(): ?string
    {
        return in_array(strtolower(parse_url($this->ticket_url ?? '', PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
            ? $this->ticket_url : null;
    }

    public function getMapUrlAttribute(): ?string
    {
        $location = $this->latitude !== null && $this->longitude !== null
            ? $this->latitude.','.$this->longitude
            : implode(', ', array_filter([$this->venue, $this->address, $this->city]));

        return $location ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($location) : null;
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if ($event->slug) return;
            $base = Str::slug($event->title) ?: 'event';
            $slug = $base;
            for ($suffix = 2; static::where('slug', $slug)->exists(); $suffix++) $slug = $base.'-'.$suffix;
            $event->slug = $slug;
        });
    }

    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }
}
