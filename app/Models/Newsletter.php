<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'type',
        'reference_id',
        'recipient_filters',
        'recipient_count',
        'sent_count',
        'failed_count',
        'status',
        'sent_at',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'recipient_filters' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'content' => 'Content',
            'event' => 'Event',
            'character' => 'Character',
            'merchandise' => 'Merchandise',
            'category' => 'Category',
            'custom' => 'Custom',
            default => 'Unknown',
        };
    }
}