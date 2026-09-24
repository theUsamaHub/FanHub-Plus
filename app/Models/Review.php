<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'title',
        'body',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function getTargetLabelAttribute(): string
    {
        return self::friendlyTargetLabel($this->reviewable_type, $this->reviewable_id, $this->reviewable);
    }

    public static function friendlyTargetLabel(string $type, int $id, ?Model $target = null): string
    {
        $label = match (class_basename($type)) {
            'Content' => 'Content',
            'MerchandiseItem' => 'Merchandise',
            'CharacterProfile' => 'Character',
            'Event' => 'Event',
            default => class_basename($type),
        };

        if (! $target) {
            return "{$label}: Unavailable resource";
        }

        $title = $target->title ?? $target->name ?? "#{$id}";

        return "{$label}: \"{$title}\"";
    }
}
