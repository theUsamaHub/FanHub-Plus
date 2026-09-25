<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'taggable');
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_tags');
    }

    public static function boot(): void
    {
        parent::boot();
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
