<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public const SECTIONS = [
        'characters' => 'Characters', 'multimedia' => 'Multimedia',
        'events' => 'Events', 'upcoming' => 'Upcoming', 'merchandise' => 'Merchandise',
        'feedback' => 'Feedback', 'privacy' => 'Privacy', 'terms' => 'Terms',
    ];

    public function explore(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('fandoms')))],
            'sort' => ['nullable', 'in:latest,popular'],
            'featured' => ['nullable', 'boolean'],
        ]);
        $query = Content::published()->with('category')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));

        if ($term = trim($filters['q'] ?? '')) {
            // Bind a literal search term (including SQL wildcard characters).
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
            $query->where(fn ($query) => $query
                ->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])
                ->orWhereRaw("LOWER(excerpt) LIKE ? ESCAPE '!'", [$term]));
        }
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($query) => $query->where('slug', $filters['category']));
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if (($filters['sort'] ?? 'latest') === 'popular') {
            $query->orderByDesc('popularity_score')->orderByDesc('view_count');
        }

        return view('public.explore', [
            'contents' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function section(string $section): View
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return view('public.coming-soon', ['title' => self::SECTIONS[$section]]);
    }

    public function account(string $section): View
    {
        $titles = ['dashboard' => 'My Dashboard', 'bookmarks' => 'Bookmarks', 'submit-content' => 'Submit Content'];
        abort_unless(isset($titles[$section]), 404);

        return view('public.coming-soon', ['title' => $titles[$section]]);
    }
}
