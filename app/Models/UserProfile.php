<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar_media_id',
        'display_name',
        'bio',
        'theme_preference',
        'font_size_preference',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }
}
