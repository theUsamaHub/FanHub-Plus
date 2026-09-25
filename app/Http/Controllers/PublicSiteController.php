<?php

namespace App\Http\Controllers;

use App\Models\CharacterProfile;
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
        $query = Content::visibleToPublic()->with(['category', 'media', 'tags']);

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
        $content->load(['category', 'submittedBy', 'media', 'tags']);
        $related = Content::visibleToPublic()->with(['category', 'media', 'tags'])
            ->where('category_id', $content->category_id)->whereKeyNot($content->id)
            ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();

        return view('public.content', compact('content', 'related'));
    }

    public function character(CharacterProfile $character): View
    {
        $character->load(['category', 'imageMedia']);
        $stories = $character->contents()->visibleToPublic()->latest('published_at')->paginate(6);

        return view('public.character', compact('character', 'stories'));
    }

    public function merchandise(MerchandiseItem $merchandise): View
    {
        $merchandise->load(['category', 'imageMedia']);

        $related = MerchandiseItem::with(['category', 'imageMedia'])->where('category_id', $merchandise->category_id)
            ->whereKeyNot($merchandise->id)->orderByDesc('view_count')->limit(4)->get();
        $savedMerchandise = $this->savedMerchandise();

        return view('public.merchandise', compact('merchandise', 'related', 'savedMerchandise'));
    }

    public function upcomingRelease(\App\Models\UpcomingRelease $upcoming_release): View
    {
        abort_unless($upcoming_release->is_published, 404);
        $upcoming_release->load(['category', 'imageMedia']);

        return view('public.upcoming-show', compact('upcoming_release'));
    }

    private function savedMerchandise(): array
    {
        return auth()->user()?->bookmarks()->where('bookmarkable_type', (new MerchandiseItem)->getMorphClass())
            ->pluck('bookmarkable_id')->all() ?? [];
    }

    public function section(string $section, Request $request, HomepageService $homepage): View
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        if ($section === 'merchandise') {
            $filter = $request->query('category', 'all');
            abort_unless(is_string($filter) && in_array($filter, ['all', ...array_keys(config('fandoms'))], true), 404);
            $items = MerchandiseItem::with(['category', 'imageMedia'])
                ->when($filter !== 'all', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $filter)))
                ->orderByDesc('view_count')->orderByDesc('id')->paginate(12)->withQueryString();

            return view('public.merchandise-index', ['items' => $items, 'activeFilter' => $filter, 'savedMerchandise' => $this->savedMerchandise()]);
        }

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
