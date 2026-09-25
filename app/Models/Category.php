<?php

namespace App\Models;

use App\Traits\HasTags;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasTags, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_media_id',
    ];

    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_media_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function characterProfiles(): HasMany
    {
        return $this->hasMany(CharacterProfile::class);
    }

    public function merchandiseItems(): HasMany
    {
        return $this->hasMany(MerchandiseItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function upcomingReleases(): HasMany
    {
        return $this->hasMany(UpcomingRelease::class);
    }

    public function chatbotFaqs(): HasMany
    {
        return $this->hasMany(ChatbotFaq::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_favorite_categories')->withPivot('created_at');
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->iconMedia?->url;
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name') && ! $category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
