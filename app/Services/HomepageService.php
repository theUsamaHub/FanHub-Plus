<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\MerchandiseItem;
use App\Models\UpcomingRelease;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomepageService
{
    private function remember(string $key, callable $callback): mixed
    {
        $version = Cache::get('homepage:version', 'initial');

        return Cache::remember('homepage:v3:'.$version.':'.today()->toDateString().':'.$key,
            config('homepage.cache_seconds'), $callback);
    }

    public function sections(): array
    {
        return $this->remember('sections', fn () => [
            'characters' => CharacterProfile::with(['category:id,name,slug', 'imageMedia'])
                ->orderByDesc('updated_at')->orderByDesc('id')->limit(7)->get(),
            'trending' => Content::visibleToPublic()->with(['category:id,name,slug', 'media'])
                ->orderByDesc('popularity_score')->orderByDesc('view_count')->orderByDesc('id')->limit(6)->get(),
            'featuredStories' => Content::visibleToPublic()->ofType('article')->where('is_featured', true)
                ->with(['category:id,name,slug', 'submittedBy:id,name', 'tags:id,name,slug', 'media'])
                ->orderByDesc('popularity_score')->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get(),
        ]);
    }

    public function releaseFilters(): Collection
    {
        return $this->remember('release-filters', fn () => Category::query()
            ->where(function ($query) {
                $query->whereHas('contents', fn ($q) => $q->visibleToPublic()->whereDate('release_date', '>=', today()))
                    ->orWhereHas('upcomingReleases', fn ($q) => $q->published()->upcoming());
            })
            ->orderByRaw("CASE slug WHEN 'anime' THEN 0 WHEN 'gaming' THEN 1 WHEN 'movies' THEN 2 WHEN 'tv-shows' THEN 3 ELSE 4 END")
            ->orderBy('name')->get(['id', 'name', 'slug']));
    }

    public function merchandise(string $category = 'all'): Collection
    {
        return $this->remember('merchandise:'.$category, fn () => MerchandiseItem::with(['category', 'imageMedia'])
            ->when($category !== 'all', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $category)))
            ->orderByDesc('view_count')->orderByDesc('id')->limit(24)->get());
    }

    public function releases(string $filter = 'all', int $limit = 6): Collection
    {
        return $this->remember('releases:'.$filter.':'.$limit, function () use ($filter, $limit) {
            $contents = collect();
            if ($filter !== 'merchandise') {
                $contents = Content::visibleToPublic()->with(['category:id,name,slug', 'media'])
                    ->whereDate('release_date', '>=', today())
                    ->when($filter !== 'all', fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $filter)))
                    ->orderBy('release_date')->orderBy('id')->limit($limit)->get()
                    ->map(fn (Content $content) => [
                        'id' => 'content-'.$content->id, 'title' => $content->title,
                        'image' => $content->artwork_url,
                        'category' => $content->category?->name ?? 'Fandom',
                        'slug' => $content->category?->slug ?? 'fandom',
                        'date' => $content->release_date,
                        'label' => $content->release_label ?: 'New release',
                        'url' => route('public.content', $content->slug),
                    ]);
            }
            $upcoming = collect();
            if ($filter !== 'merchandise') {
                $upcoming = UpcomingRelease::published()->upcoming()->with(['category:id,name,slug', 'imageMedia'])
                    ->when($filter !== 'all', fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $filter)))
                    ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')->orderBy('release_date')->orderBy('sort_order')->orderBy('id')
                    ->limit($limit)->get()->map(fn (UpcomingRelease $release) => [
                        'id' => 'upcoming-'.$release->id, 'title' => $release->title,
                        'image' => $release->artwork_url,
                        'category' => $release->category?->name ?? $release->kind_label,
                        'slug' => $release->category?->slug ?? ($release->kind === 'merchandise' ? 'merchandise' : 'fandom'),
                        'date' => $release->release_date,
                        'label' => $release->release_label ?: $release->kind_label,
                        'url' => route('public.upcoming-release', $release->slug),
                    ]);
            }
            $merchandise = collect();
            if (in_array($filter, ['all', 'merchandise'], true)) {
                $merchandise = MerchandiseItem::upcoming()->with('imageMedia')
                    ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
                    ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')->orderBy('release_date')->orderBy('id')
                    ->limit($limit)->get()->map(fn (MerchandiseItem $item) => [
                        'id' => 'merchandise-'.$item->id, 'title' => $item->name,
                        'image' => $item->imageMedia?->url ?? asset(config('homepage.images.upcoming')),
                        'category' => 'Merchandise', 'slug' => 'merchandise', 'date' => $item->release_date,
                        'label' => str_replace('_', ' ', $item->tag), 'url' => route('public.merchandise', $item->slug),
                    ]);
            }

            return $contents->concat($upcoming)->concat($merchandise)
                ->sortBy(fn ($item) => $item['date']?->format('Y-m-d') ?? '9999-12-31')->take($limit)->values();
        });
    }

    public function paginatedReleases(string $filter): LengthAwarePaginator
    {
        $content = Content::visibleToPublic()
            ->leftJoin('categories', 'categories.id', '=', 'contents.category_id')
            ->selectRaw("contents.id, contents.title, contents.slug, contents.release_date, categories.name as category, categories.slug as category_slug, 'content' as kind, NULL as tag, contents.release_label")
            ->whereDate('contents.release_date', '>=', today())
            ->when($filter === 'merchandise', fn ($query) => $query->whereRaw('1 = 0'))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->where('categories.slug', $filter));
        $upcoming = UpcomingRelease::published()->upcoming()
            ->leftJoin('categories', 'categories.id', '=', 'upcoming_releases.category_id')
            ->selectRaw("upcoming_releases.id, upcoming_releases.title, upcoming_releases.slug, upcoming_releases.release_date, COALESCE(categories.name, upcoming_releases.kind) as category, categories.slug as category_slug, 'upcoming' as kind, NULL as tag, upcoming_releases.release_label")
            ->when($filter === 'merchandise', fn ($query) => $query->whereRaw('1 = 0'))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->where('categories.slug', $filter));
        $merchandise = MerchandiseItem::upcoming()
            ->selectRaw("id, name as title, slug, release_date, 'Merchandise' as category, 'merchandise' as category_slug, 'merchandise' as kind, tag, NULL as release_label")
            ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->whereRaw('1 = 0'));

        $paginator = DB::query()->fromSub(
            $content->toBase()->unionAll($upcoming->toBase())->unionAll($merchandise->toBase()),
            'releases'
        )
            ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('release_date')->orderBy('kind')->orderBy('id')->paginate(12)->withQueryString();

        $ids = collect($paginator->items())->pluck('id');
        $kinds = collect($paginator->items())->pluck('kind', 'id');
        $contentArt = Content::with('media')->whereIn('id', $ids->filter(fn ($id) => $kinds[$id] === 'content')->values())
            ->get()->keyBy('id')->map(fn (Content $item) => $item->artwork_url);
        $upcomingArt = UpcomingRelease::with('imageMedia')->whereIn('id', $ids->filter(fn ($id) => $kinds[$id] === 'upcoming')->values())
            ->get()->keyBy('id')->map(fn (UpcomingRelease $item) => $item->artwork_url);
        $merchArt = MerchandiseItem::with('imageMedia')->whereIn('id', $ids->filter(fn ($id) => $kinds[$id] === 'merchandise')->values())
            ->get()->keyBy('id')->map(fn (MerchandiseItem $item) => $item->imageMedia?->url);

        return $paginator->through(function ($item) use ($contentArt, $upcomingArt, $merchArt) {
            $image = match ($item->kind) {
                'content' => $contentArt[$item->id] ?? null,
                'upcoming' => $upcomingArt[$item->id] ?? null,
                default => $merchArt[$item->id] ?? null,
            };
            $slug = $item->category_slug
                ?? ($item->kind === 'merchandise' ? 'merchandise' : ($item->kind === 'upcoming' ? 'fandom' : 'fandom'));

            return [
                'title' => $item->title, 'category' => $item->category ?? 'Fandom',
                'slug' => $slug,
                'date' => $item->release_date ? Carbon::parse($item->release_date) : null,
                'label' => $item->tag ? str_replace('_', ' ', $item->tag) : ($item->release_label ?: 'New release'),
                'image' => $image ?? asset(config('homepage.images.upcoming')),
                'url' => match ($item->kind) {
                    'content' => route('public.content', $item->slug),
                    'upcoming' => route('public.upcoming-release', $item->slug),
                    default => route('public.merchandise', $item->slug),
                },
            ];
        });
    }
}
