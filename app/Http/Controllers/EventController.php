<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request, bool $nearby = false)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120', Rule::exists('categories', 'slug')],
            'city' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(array_keys(config('events.types')))],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'when' => ['nullable', Rule::in(['all', 'upcoming', 'past'])],
            'sort' => ['nullable', Rule::in(['soonest', 'popular', 'latest'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ] + ($nearby ? [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['sometimes', 'integer', Rule::in(config('events.nearby_radii'))],
        ] : []));
        $hasFilters = $nearby || collect($filters)->except('page')->contains(fn ($value) => filled($value));
        $featured = $nearby ? collect() : Event::forUser(auth()->user())->published()->where('is_featured', true)
            ->where('start_at', '>=', now())->with(['category', 'coverMedia'])
            ->orderByDesc('popularity_score')->orderBy('start_at')->orderBy('id')
            ->limit(config('events.featured_limit'))->get();
        $query = Event::forUser(auth()->user())->published()->with(['category', 'coverMedia']);
        if (! $hasFilters && $featured->isNotEmpty()) $query->whereNotIn('id', $featured->modelKeys());
        $term = trim($filters['q'] ?? '');
        if ($term !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(venue) LIKE ? ESCAPE '!'", [$pattern]));
        }
        if (! empty($filters['category'])) $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        if (! empty($filters['city'])) $query->where('city', $filters['city']);
        if (! empty($filters['type'])) $query->where('event_type', $filters['type']);
        if (! empty($filters['date'])) {
            $query->whereDate('start_at', '<=', $filters['date'])
                ->whereRaw('DATE(COALESCE(end_at, start_at)) >= ?', [$filters['date']]);
        }
        if (($filters['when'] ?? '') === 'upcoming') $query->where('start_at', '>=', now());
        if (($filters['when'] ?? '') === 'past') $query->whereRaw('COALESCE(end_at, start_at) < ?', [now()]);
        $nearbyCandidates = $nearby ? clone $query : null;
        if ($nearby) {
            $query->withinRadius((float) $filters['latitude'], (float) $filters['longitude'], (int) ($filters['radius'] ?? 5));
            if (empty($filters['sort'])) $query->orderBy('distance_km');
            $sort = $filters['sort'] ?? null;
            match ($sort) {
                'popular' => $query->orderByDesc('popularity_score')->orderByDesc('view_count'),
                'latest' => $query->orderByDesc('created_at'),
                'soonest' => $query->orderByRaw('CASE WHEN COALESCE(end_at, start_at) >= ? THEN 0 ELSE 1 END', [now()]),
                default => null,
            };
            $query->orderBy('start_at')->orderBy('id');
        } else {
            match ($filters['sort'] ?? 'soonest') {
                'popular' => $query->orderByDesc('popularity_score')->orderByDesc('view_count'),
                'latest' => $query->orderByDesc('created_at'),
                default => $query->orderByRaw('CASE WHEN COALESCE(end_at, start_at) >= ? THEN 0 ELSE 1 END', [now()]),
            };
            $query->orderBy('start_at')->orderBy('id');
        }
        $events = $query->paginate(config('events.per_page'))->withQueryString()->fragment('explore-events');

        if ($nearby) {
            $nearestDistance = $events->total() === 0
                ? $nearbyCandidates->withoutEagerLoads()->reorder()
                    ->withinRadius((float) $filters['latitude'], (float) $filters['longitude'], 20016)
                    ->orderBy('distance_km')->first()?->distance_km
                : null;
            return response()->json([
                'html' => view('events.partials.results', compact('events', 'hasFilters'))->render(),
                'total' => $events->total(),
                'nearest_distance_km' => $nearestDistance === null ? null : round((float) $nearestDistance, 1),
            ])->header('Cache-Control', 'no-store, private');
        }

        return view('events.index', [
            'events' => $events, 'featured' => $featured,
            'filters' => $filters, 'hasFilters' => $hasFilters,
            'categories' => Category::whereHas('events', fn ($q) => $q->published())->orderBy('name')->get(['id', 'name', 'slug']),
            'cities' => Event::published()->select('city')->distinct()->orderBy('city')->pluck('city'),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->status === 'published', 404);
        $event->load(['category', 'coverMedia', 'galleryMedia', 'content']);
        $event->recordView();
        $related = Event::forUser(auth()->user())->published()->with(['category', 'coverMedia'])->whereKeyNot($event->id)
            ->where('category_id', $event->category_id)->where('start_at', '>=', now())
            ->orderBy('start_at')->limit(3)->get();

        return view('events.show', compact('event', 'related'));
    }

    public function calendar(Event $event)
    {
        abort_unless($event->status === 'published', 404);
        $escape = fn ($text) => str_replace(["\\", "\r\n", "\r", "\n", ';', ','], ['\\\\', '\\n', '\\n', '\\n', '\\;', '\\,'], $text ?? '');
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Fan Hub Plus//Events//EN', 'CALSCALE:GREGORIAN', 'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$event->start_at->copy()->utc()->format('Ymd\THis\Z'),
        ];
        if ($event->end_at) $lines[] = 'DTEND:'.$event->end_at->copy()->utc()->format('Ymd\THis\Z');
        $lines = array_merge($lines, ['SUMMARY:'.$escape($event->title),
            'DESCRIPTION:'.$escape(strip_tags($event->description ?? '')),
            'LOCATION:'.$escape(implode(', ', array_filter([$event->venue, $event->address, $event->city]))),
            'URL:'.route('events.show', $event->slug), 'END:VEVENT', 'END:VCALENDAR']);
        $body = collect($lines)->map(function ($line) {
            $parts = [];
            while (strlen($line) > 74) {
                $part = mb_strcut($line, 0, 74, 'UTF-8');
                $parts[] = $part;
                $line = substr($line, strlen($part));
            }
            $parts[] = $line;
            return implode("\r\n ", $parts);
        })->implode("\r\n")."\r\n";

        return response($body, 200, ['Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"']);
    }
}
