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
        $query = Content::forUser(auth()->user())->visibleToPublic()->with(['category', 'media', 'tags']);

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

    public function fandom(\App\Models\Category $category, Request $request): View
    {
        $fandomConfig = config('fandoms.'.$category->slug, []);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:latest,popular,trending,alphabetical'],
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
            'year' => ['nullable', 'integer', 'between:1900,2200'],
            'type' => ['nullable', 'in:article,video,audio,image'],
        ]);

        $query = Content::forUser(auth()->user())->visibleToPublic()->with(['category', 'media', 'tags'])
            ->whereHas('category', fn ($q) => $q->where('slug', $category->slug));

        if ($term = trim($filters['q'] ?? '')) {
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
            $query->where(fn ($q) => $q
                ->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])
                ->orWhereRaw("LOWER(excerpt) LIKE ? ESCAPE '!'", [$term]));
        }
        if (! empty($filters['type'])) {
            $query->ofType($filters['type']);
        }
        if (($filters['sort'] ?? 'latest') === 'popular') {
            $query->orderByDesc('popularity_score')->orderByDesc('view_count');
        } elseif (($filters['sort'] ?? '') === 'trending') {
            $query->orderByDesc('view_count')->orderByDesc('published_at');
        } elseif (($filters['sort'] ?? '') === 'alphabetical') {
            $query->orderBy('title');
        } else {
            $query->orderByDesc('published_at');
        }
        if (! empty($filters['year'])) $query->whereYear('release_date', $filters['year']);
        if (! empty($filters['tag'])) $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag']));

        // Get featured content for this fandom
        $featured = Content::forUser(auth()->user())->visibleToPublic()->with(['category', 'media', 'tags'])
            ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
            ->where('is_featured', true)
            ->orderByDesc('popularity_score')
            ->limit(4)->get();

        // Get trending content for this fandom
        $trending = Content::forUser(auth()->user())->visibleToPublic()->with(['category', 'media', 'tags'])
            ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
            ->orderByDesc('view_count')
            ->limit(8)->get();

        // Stats
        $stats = [
            'total_content' => Content::forUser(auth()->user())->visibleToPublic()
                ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
                ->count(),
            'total_views' => Content::forUser(auth()->user())->visibleToPublic()
                ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
                ->sum('view_count'),
            'total_creators' => Content::forUser(auth()->user())->visibleToPublic()
                ->whereHas('category', fn ($q) => $q->where('slug', $category->slug))
                ->distinct('submitted_by')
                ->count('submitted_by'),
        ];

        $contents = $query->paginate(12)->withQueryString();
        $categories = \App\Models\Category::orderBy('name')->get();
        $tags = \App\Models\Tag::whereHas('contents', fn ($q) => $q->visibleToPublic()->whereHas('category', fn ($q2) => $q2->where('slug', $category->slug)))->orderBy('name')->get();

        return view('public.fandom', compact(
            'category', 'fandomConfig', 'filters', 'contents', 'featured', 'trending', 'stats', 'categories', 'tags'
        ));
    }

    public function content(Content $content): View
    {
        abort_unless(Content::forUser(auth()->user())->visibleToPublic()->whereKey($content->id)->exists(), 404);
        $content->load(['category', 'submittedBy', 'media', 'tags']);
        app(\App\Services\MemberLibrary::class)->viewed($content);
        $related = Content::forUser(auth()->user())->visibleToPublic()->with(['category', 'media', 'tags'])
            ->where('category_id', $content->category_id)->whereKeyNot($content->id)
            ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();
        // Characters linked ONLY through the character_contents junction —
        // this enforces the many-to-many relation so unrelated Characters
        // never appear on a Content detail page.
        $characters = $content->characters()->with(['category', 'imageMedia'])->orderBy('name')->get();
        // Merchandise scoped to this specific Content record
        $merchandise = $content->merchandiseItems()->with(['category', 'imageMedia', 'character'])
            ->orderByDesc('view_count')->limit(12)->get();
        // Events tied to this Content record
        $events = $content->events()->with(['category', 'coverMedia'])
            ->published()
            ->whereRaw('COALESCE(end_at, start_at) >= ?', [now()])
            ->orderBy('start_at')->limit(6)->get();

        $gallery = $content->mediaByRole('gallery')->filter(fn ($media) => $media->isImage() && $media->hasValidPath());
        $trailers = $content->mediaByRole('trailer')->filter(fn ($media) => $media->isVideo() && $media->hasValidPath());
        $audioClips = $content->mediaByRole('audio_clip')->filter(fn ($media) => $media->isAudio() && $media->hasValidPath());
        $attachments = $content->mediaByRole('attachment')->filter(fn ($media) => $media->isDocument() && $media->hasValidPath());
        $saved = auth()->user()?->bookmarks()->where([
            'bookmarkable_type' => $content->getMorphClass(), 'bookmarkable_id' => $content->id,
        ])->exists() ?? false;
        $watchlisted = auth()->check() && \App\Models\ActivityLog::where([
            'user_id' => auth()->id(), 'event' => 'member.watchlisted',
            'auditable_type' => $content->getMorphClass(), 'auditable_id' => $content->id,
        ])->exists();
        $savedMerchandise = $this->savedMerchandise();

        return view('public.content', compact('content', 'related', 'characters', 'merchandise', 'events',
            'gallery', 'trailers', 'audioClips', 'attachments', 'saved', 'watchlisted', 'savedMerchandise'));
    }

    public function character(CharacterProfile $character): View
    {
        $character->load(['category', 'imageMedia']);
        app(\App\Services\MemberLibrary::class)->viewed($character);
        $stories = $character->contents()->visibleToPublic()->latest('published_at')->paginate(6);
        $merchandise = $character->merchandiseItems()->with(['category', 'imageMedia', 'content'])
            ->orderByDesc('view_count')->limit(12)->get();

        return view('public.character', compact('character', 'stories', 'merchandise'));
    }

    public function merchandise(MerchandiseItem $merchandise): View
    {
        $merchandise->load(['category', 'imageMedia', 'content' => fn ($query) => $query->visibleToPublic(), 'character']);
        app(\App\Services\MemberLibrary::class)->viewed($merchandise);

        // Prefer related merchandise within the same Content, then fall back to category-level.
        $related = MerchandiseItem::forUser(auth()->user())->with(['category', 'imageMedia', 'content', 'character'])
            ->where(function ($q) use ($merchandise) {
                if ($merchandise->content_id) {
                    $q->where('content_id', $merchandise->content_id);
                }
                $q->orWhere('category_id', $merchandise->category_id);
            })
            ->whereKeyNot($merchandise->id)
            ->when($merchandise->content_id, fn ($query) => $query->orderByRaw('CASE WHEN content_id = ? THEN 0 ELSE 1 END', [$merchandise->content_id]))
            ->orderByDesc('view_count')->orderByDesc('id')->limit(4)->get();
        $savedMerchandise = $this->savedMerchandise();

        return view('public.merchandise', compact('merchandise', 'related', 'savedMerchandise'));
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
        if ($section === 'privacy') return view('public.privacy');

        if ($section === 'merchandise') {
            $filters = $request->validate([
                'q' => ['nullable', 'string', 'max:120'],
                'category' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', 'in:released,upcoming'],
                'tag' => ['nullable', 'in:limited_edition,pre_order,collectible,standard'],
                'sort' => ['nullable', 'in:newest,popular,name'],
                'content' => ['nullable', 'integer', 'min:1'],
            ]);
            $filters = array_merge(['q' => '', 'category' => 'all', 'status' => '', 'tag' => '', 'sort' => 'newest'], array_filter($filters, fn ($value) => $value !== null));
            $filters['q'] = trim($filters['q']);
            $categories = \App\Models\Category::orderBy('name')->get(['id', 'name', 'slug']);
            // Keep existing fandom URLs working, including empty fandoms.
            abort_unless(in_array($filters['category'], ['all', ...$categories->pluck('slug')->all(), ...array_keys(config('fandoms'))], true), 404);
            $query = MerchandiseItem::forUser(auth()->user())->with(['category', 'imageMedia']);
            $linkedContent = ! empty($filters['content'])
                ? Content::visibleToPublic()->findOrFail($filters['content']) : null;
            if ($linkedContent) $query->where('content_id', $linkedContent->id);
            if ($filters['q'] !== '') {
                $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($filters['q'])).'%';
                $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$term]);
            }
            if ($filters['category'] !== 'all') {
                $query->whereHas('category', fn ($query) => $query->where('slug', $filters['category']));
            }
            if ($filters['status'] !== '') $query->where('is_upcoming', $filters['status'] === 'upcoming');
            if ($filters['tag'] !== '') $query->withTag($filters['tag']);
            match ($filters['sort']) {
                'popular' => $query->orderByDesc('view_count'),
                'name' => $query->orderBy('name'),
                default => $query->orderByDesc('created_at'),
            };
            $items = $query->orderByDesc('id')->paginate(12)->withQueryString();
            $hasFilters = $linkedContent !== null || $filters['q'] !== '' || $filters['category'] !== 'all' || $filters['status'] !== '' || $filters['tag'] !== '';

            return view('public.merchandise-index', compact('items', 'filters', 'categories', 'hasFilters', 'linkedContent') + ['savedMerchandise' => $this->savedMerchandise()]);
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
