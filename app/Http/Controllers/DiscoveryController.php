<?php

namespace App\Http\Controllers;

use App\Models\{Category, CharacterProfile, Content, Tag};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscoveryController extends Controller
{
    public function characters(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:120', 'category' => 'nullable|string|max:100', 'sort' => 'nullable|in:latest,alphabetical']);
        $query = CharacterProfile::with(['category', 'imageMedia']);
        if ($q = trim($filters['q'] ?? '')) {
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(fn ($query) => $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$term])->orWhereRaw("LOWER(bio) LIKE ? ESCAPE '!'", [$term]));
        }
        if ($filters['category'] ?? null) $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        ($filters['sort'] ?? '') === 'alphabetical' ? $query->orderBy('name') : $query->latest('id');
        return view('public.discovery', ['kind' => 'characters', 'title' => 'The faces behind the stories.',
            'intro' => 'Meet the characters who make your favorite worlds unforgettable.', 'items' => $query->paginate(12)->withQueryString(),
            'filters' => $filters, 'categories' => Category::orderBy('name')->get(), 'tags' => collect()]);
    }

    public function multimedia(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:120', 'category' => 'nullable|string|max:100',
            'type' => 'nullable|in:image,video,audio', 'sort' => 'nullable|in:latest,popular,alphabetical',
            'tag' => ['nullable', 'integer', Rule::exists('tags', 'id')], 'year' => 'nullable|integer|between:1900,2200']);
        $query = Content::visibleToPublic()->whereIn('type', ['image', 'video', 'audio'])->with(['category', 'media']);
        if ($q = trim($filters['q'] ?? '')) {
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(fn ($query) => $query->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])->orWhereRaw("LOWER(excerpt) LIKE ? ESCAPE '!'", [$term]));
        }
        if ($filters['category'] ?? null) $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        if ($filters['type'] ?? null) $query->where('type', $filters['type']);
        if ($filters['tag'] ?? null) $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag']));
        if ($filters['year'] ?? null) $query->whereYear('release_date', $filters['year']);
        match ($filters['sort'] ?? 'latest') {
            'popular' => $query->orderByDesc('popularity_score')->orderByDesc('view_count'),
            'alphabetical' => $query->orderBy('title'), default => $query->latest('published_at'),
        };
        return view('public.discovery', ['kind' => 'multimedia', 'title' => 'A world beyond words.',
            'intro' => 'Fan art, trailers, soundtracks and more. Find your next favorite moment.', 'items' => $query->orderByDesc('id')->paginate(12)->withQueryString(),
            'filters' => $filters, 'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::whereHas('contents', fn ($q) => $q->visibleToPublic()->whereIn('type', ['image', 'video', 'audio']))->orderBy('name')->get()]);
    }
}
