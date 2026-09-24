<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Content;
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
        $version = Cache::get('homepage:version', 'initial');

        return Cache::remember('homepage:v2:'.$version.':'.today()->toDateString().':'.$key,
            config('homepage.cache_seconds'), $callback);
    }

    public function sections(): array
    {
        return $this->remember('sections', fn () => [
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
            ->whereHas('contents', fn ($query) => $query->visibleToPublic()->whereDate('release_date', '>=', today()))
            ->orderByRaw("CASE slug WHEN 'anime' THEN 0 WHEN 'gaming' THEN 1 WHEN 'movies' THEN 2 WHEN 'tv-shows' THEN 3 ELSE 4 END")
            ->orderBy('name')->get(['id', 'name', 'slug']));
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

            return $contents->concat($merchandise)->sortBy(fn ($item) => $item['date']?->format('Y-m-d') ?? '9999-12-31')->take($limit)->values();
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
        $merchandise = MerchandiseItem::upcoming()
            ->selectRaw("id, name as title, slug, release_date, 'Merchandise' as category, 'merchandise' as category_slug, 'merchandise' as kind, tag, NULL as release_label")
            ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
            ->when(! in_array($filter, ['all', 'merchandise'], true), fn ($query) => $query->whereRaw('1 = 0'));

        return DB::query()->fromSub($content->toBase()->unionAll($merchandise->toBase()), 'releases')
            ->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('release_date')->orderBy('kind')->orderBy('id')->paginate(12)->withQueryString()
            ->through(fn ($item) => [
                'title' => $item->title, 'category' => $item->category ?? 'Fandom',
                'date' => $item->release_date ? Carbon::parse($item->release_date) : null,
                'label' => $item->tag ? str_replace('_', ' ', $item->tag) : ($item->release_label ?: 'New release'),
                'url' => route($item->kind === 'content' ? 'public.content' : 'public.merchandise', $item->slug),
            ]);
    }
}
