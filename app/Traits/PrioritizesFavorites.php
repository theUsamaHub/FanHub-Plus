<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait PrioritizesFavorites
{
    // Apply before the feed's normal sorting and pagination. This ranks favourites;
    // it never restricts the result set or replaces visibility/category filters.
    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        $ids = $user?->favoriteCategoryIds() ?? [];
        if ($ids === []) return $query;

        $column = $query->getModel()->qualifyColumn('category_id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return $query->orderByRaw("CASE WHEN $column IN ($placeholders) THEN 0 ELSE 1 END", $ids);
    }
}
