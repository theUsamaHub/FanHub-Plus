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
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:latest,popular,alphabetical'],
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
            'year' => ['nullable', 'integer', 'between:1900,2200'],
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
        if (($filters['sort'] ?? '') === 'alphabetical') $query->orderBy('title');
        if (! empty($filters['year'])) $query->whereYear('release_date', $filters['year']);
        if (! empty($filters['tag'])) $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag']));

        return view('public.explore', [
            'contents' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString(),
            'filters' => $filters,
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'tags' => \App\Models\Tag::whereHas('contents', fn ($q) => $q->visibleToPublic())->orderBy('name')->get(),
        ]);
    }

    public function content(Content $content): View
    {
        abort_unless(Content::visibleToPublic()->whereKey($content->id)->exists(), 404);
        $content->load(['category', 'submittedBy', 'media', 'tags']);
        app(\App\Services\MemberLibrary::class)->viewed($content);
        $related = Content::visibleToPublic()->with(['category', 'media', 'tags'])
            ->where('category_id', $content->category_id)->whereKeyNot($content->id)
            ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();

        return view('public.content', compact('content', 'related'));
    }

    public function character(CharacterProfile $character): View
    {
        $character->load(['category', 'imageMedia']);
        app(\App\Services\MemberLibrary::class)->viewed($character);
        $stories = $character->contents()->visibleToPublic()->latest('published_at')->paginate(6);

        return view('public.character', compact('character', 'stories'));
    }

    public function merchandise(MerchandiseItem $merchandise): View
    {
        $merchandise->load(['category', 'imageMedia']);
        app(\App\Services\MemberLibrary::class)->viewed($merchandise);

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

    public function section(string $section, Request $request, HomepageService $homepage): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        if ($section === 'characters') return app(DiscoveryController::class)->characters($request);
        if ($section === 'multimedia') return app(DiscoveryController::class)->multimedia($request);
        if ($section === 'feedback') return redirect()->route('user.feedback');

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

    public function account(string $section): \Illuminate\Http\RedirectResponse
    {
        $titles = ['dashboard' => 'My Dashboard', 'bookmarks' => 'Bookmarks', 'submit-content' => 'Submit Content'];
        abort_unless(isset($titles[$section]), 404);

        return redirect()->route(match ($section) {
            'dashboard' => 'dashboard', 'bookmarks' => 'user.bookmarks', 'submit-content' => 'user.submissions.create',
        });
    }
}
