<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'subscribed_at',
        'unsubscribed_at',
        'ip_address',
        'preferences',
        'status',
        'unsubscribe_token',
        'last_email_sent_at',
        'email_count',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'last_email_sent_at' => 'datetime',
            'preferences' => 'array',
            'email_count' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNotNull('subscribed_at')->whereNull('unsubscribed_at');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeWithCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereJsonContains('preferences->categories', $categoryId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->subscribed_at && !$this->unsubscribed_at;
    }

    public function getPreferences(): array
    {
        return $this->preferences ?? [];
    }

    public function hasCategoryPreference(int $categoryId): bool
    {
        $categories = $this->getPreferences()['categories'] ?? [];
        return in_array($categoryId, $categories);
    }

    public function generateUnsubscribeToken(): string
    {
        $this->unsubscribe_token = \Illuminate\Support\Str::random(64);
        $this->save();
        return $this->unsubscribe_token;
    }
}