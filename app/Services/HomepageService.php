<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Event;
use App\Models\MerchandiseItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomepageService
{
    private function remember(string $key, callable $callback): mixed
    {
        // Personalized result sets must never enter the shared guest cache.
        if (auth()->check()) return $callback();

        $version = Cache::get('homepage:version', 'initial');

        return Cache::remember('homepage:v4:'.$version.':'.today()->toDateString().':'.$key,
            config('homepage.cache_seconds'), $callback);
    }

    public function sections(): array
    {
        return $this->remember('sections', fn () => [
            'characters' => CharacterProfile::forUser(auth()->user())->with(['category:id,name,slug', 'imageMedia'])
                ->orderByDesc('updated_at')->orderByDesc('id')->limit(7)->get(),
            'trending' => Content::forUser(auth()->user())->visibleToPublic()->with(['category:id,name,slug', 'media'])
                ->orderByDesc('popularity_score')->orderByDesc('view_count')->orderByDesc('id')->limit(6)->get(),
            'featuredStories' => Content::forUser(auth()->user())->visibleToPublic()->ofType('article')->where('is_featured', true)
                ->with(['category:id,name,slug', 'submittedBy:id,name', 'tags:id,name,slug', 'media'])
                ->orderByDesc('popularity_score')->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get(),
            'homeEvents' => Event::forUser(auth()->user())->published()->with(['category:id,name,slug', 'coverMedia'])
                ->where(fn ($query) => $query->where('start_at', '>=', now())->orWhere('end_at', '>=', now()))
                ->orderByDesc('is_featured')->orderBy('start_at')->orderBy('id')->limit(5)->get(),
            'multimediaItems' => Content::forUser(auth()->user())->visibleToPublic()->where('is_user_submitted', true)
                ->with(['category:id,name,slug', 'media'])
                ->orderByDesc('published_at')->orderByDesc('id')->limit(20)->get(),
        ]);
    }

    public function releaseFilters(): Collection
    {
        return $this->remember('release-filters', fn () => Category::query()
            ->whereHas('contents', fn ($q) => $q->visibleToPublic()->upcoming())
            ->orderByRaw("CASE slug WHEN 'anime' THEN 0 WHEN 'gaming' THEN 1 WHEN 'movies' THEN 2 WHEN 'tv-shows' THEN 3 ELSE 4 END")
            ->orderBy('name')->get(['id', 'name', 'slug']));
    }

    public function merchandise(string $category = 'all'): Collection
    {
        return $this->remember('merchandise:'.$category, fn () => MerchandiseItem::forUser(auth()->user())->with(['category', 'imageMedia'])
            ->when($category !== 'all', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $category)))
            ->orderByDesc('view_count')->orderByDesc('id')->limit(24)->get());
    }

    public function releases(string $filter = 'all', int $limit = 6): Collection
    {
        return $this->remember('releases:'.$filter.':'.$limit, function () use ($filter, $limit) {
            $contents = collect();
            if ($filter !== 'merchandise') {
                $contents = Content::forUser(auth()->user())->visibleToPublic()->with(['category:id,name,slug', 'media'])
                    ->upcoming()
                    ->when($filter !== 'all', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $filter)))
                    ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')->orderBy('release_date')->orderBy('id')
                    ->limit($limit)->get()
                    ->map(fn (Content $content) => [
                        'id' => 'content-'.$content->id, 'title' => $content->title,
                        'favorite' => in_array($content->category_id, auth()->user()?->favoriteCategoryIds() ?? []),
                        'image' => $content->artwork_url,
                        'category' => $content->category?->name ?? 'Fandom',
                        'slug' => $content->category?->slug ?? 'fandom',
                        'date' => $content->release_date,
                        'label' => $content->release_label ?: ($content->kind_label ?? 'New release'),
                        'url' => route('public.content', $content->slug),
                    ]);
            }
            $merchandise = collect();
            if (in_array($filter, ['all', 'merchandise'], true)) {
                $merchandise = MerchandiseItem::forUser(auth()->user())->upcoming()->with('imageMedia')
                    ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
                    ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')->orderBy('release_date')->orderBy('id')
                    ->limit($limit)->get()->map(fn (MerchandiseItem $item) => [
                        'id' => 'merchandise-'.$item->id, 'title' => $item->name,
                        'favorite' => in_array($item->category_id, auth()->user()?->favoriteCategoryIds() ?? []),
                        'image' => $item->imageMedia?->url ?? asset(config('homepage.images.upcoming')),
                        'category' => 'Merchandise', 'slug' => 'merchandise', 'date' => $item->release_date,
                        'label' => str_replace('_', ' ', $item->tag), 'url' => route('public.merchandise', $item->slug),
                    ]);
            }

            return $contents->concat($merchandise)
                ->sortBy(fn ($item) => ($item['favorite'] ? '0' : '1').($item['date']?->format('Y-m-d') ?? '9999-12-31'))->take($limit)->values();
        });
    }

    public function paginatedReleases(string $filter): LengthAwarePaginator
    {
        $content = Content::visibleToPublic()
            ->leftJoin('categories', 'categories.id', '=', 'contents.category_id')
            ->selectRaw("contents.id, contents.category_id, contents.title, contents.slug, contents.release_date, contents.kind, categories.name as category, categories.slug as category_slug, 'content' as source_kind, NULL as tag, contents.release_label")
            ->upcoming()
            ->when($filter === 'merchandise', fn ($query) => $query->whereRaw('1 = 0'))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->where('categories.slug', $filter));
        $merchandise = MerchandiseItem::upcoming()
            ->selectRaw("id, category_id, name as title, slug, release_date, NULL as kind, 'Merchandise' as category, 'merchandise' as category_slug, 'merchandise' as source_kind, tag, NULL as release_label")
            ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->whereRaw('1 = 0'));

        $favoriteIds = auth()->user()?->favoriteCategoryIds() ?? [];
        $paginator = DB::query()->fromSub(
            $content->toBase()->unionAll($merchandise->toBase()),
            'releases'
        )
            ->when($favoriteIds !== [], fn ($query) => $query->orderByRaw('CASE WHEN category_id IN ('.implode(',', array_fill(0, count($favoriteIds), '?')).') THEN 0 ELSE 1 END', $favoriteIds))
            ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('release_date')->orderBy('source_kind')->orderBy('id')->paginate(12)->withQueryString();

        $ids = collect($paginator->items())->pluck('id');
        $kinds = collect($paginator->items())->pluck('source_kind', 'id');
        $contentArt = Content::with('media')->whereIn('id', $ids->filter(fn ($id) => $kinds[$id] === 'content')->values())
            ->get()->keyBy('id')->map(fn (Content $item) => $item->artwork_url);
        $merchArt = MerchandiseItem::forUser(auth()->user())->with('imageMedia')->whereIn('id', $ids->filter(fn ($id) => $kinds[$id] === 'merchandise')->values())
            ->get()->keyBy('id')->map(fn (MerchandiseItem $item) => $item->imageMedia?->url);

        return $paginator->through(function ($item) use ($contentArt, $merchArt) {
            $image = match ($item->source_kind) {
                'content' => $contentArt[$item->id] ?? null,
                default => $merchArt[$item->id] ?? null,
            };
            $slug = $item->category_slug
                ?? ($item->source_kind === 'merchandise' ? 'merchandise' : 'fandom');

            return [
                'title' => $item->title, 'category' => $item->category ?? 'Fandom',
                'slug' => $slug,
                'date' => $item->release_date ? Carbon::parse($item->release_date) : null,
                'label' => $item->tag ? str_replace('_', ' ', $item->tag) : ($item->release_label ?: 'New release'),
                'image' => $image ?? asset(config('homepage.images.upcoming')),
                'url' => match ($item->source_kind) {
                    'content' => route('public.content', $item->slug),
                    default => route('public.merchandise', $item->slug),
                },
            ];
        });
    }
}
