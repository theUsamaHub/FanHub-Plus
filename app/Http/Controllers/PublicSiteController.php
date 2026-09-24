<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\MerchandiseItem;
use App\Services\HomepageService;
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
            'type' => ['nullable', 'in:article,video,audio,image'],
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
        if (! empty($filters['type'])) {
            $query->ofType($filters['type']);
        }
        if (($filters['sort'] ?? 'latest') === 'popular') {
            $query->orderByDesc('popularity_score')->orderByDesc('view_count');
        }

        return view('public.explore', [
            'contents' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function content(Content $content): View
    {
        abort_unless(Content::visibleToPublic()->whereKey($content->id)->exists(), 404);
        $content->load(['category', 'submittedBy']);

        return view('public.content', compact('content'));
    }

    public function merchandise(MerchandiseItem $merchandise): View
    {
        $merchandise->load('category');

        return view('public.merchandise', compact('merchandise'));
    }

    public function section(string $section, Request $request, HomepageService $homepage): View
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        if ($section === 'upcoming') {
            $filters = $homepage->releaseFilters();
            $filter = $request->query('category', 'all');
            abort_unless(is_string($filter) && in_array($filter, ['all', 'merchandise', ...$filters->pluck('slug')->all()], true), 404);

            return view('public.upcoming', ['releases' => $homepage->paginatedReleases($filter), 'filters' => $filters, 'activeFilter' => $filter]);
        }

        return view('public.coming-soon', ['title' => self::SECTIONS[$section]]);
    }

    public function account(string $section): View
    {
        $titles = ['dashboard' => 'My Dashboard', 'bookmarks' => 'Bookmarks', 'submit-content' => 'Submit Content'];
        abort_unless(isset($titles[$section]), 404);

        return view('public.coming-soon', ['title' => $titles[$section]]);
    }
}
