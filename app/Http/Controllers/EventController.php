<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
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
        ]);
        $hasFilters = collect($filters)->except('page')->contains(fn ($value) => filled($value));
        $featured = $hasFilters ? collect() : Event::published()->where('is_featured', true)
            ->where('start_at', '>=', now())->with(['category', 'coverMedia'])
            ->orderByDesc('popularity_score')->orderBy('start_at')->orderBy('id')
            ->limit(config('events.featured_limit'))->get();
        $query = Event::published()->with(['category', 'coverMedia']);
        // Unfiltered browsing avoids repeating the selected stories in the grid.
        // A search/filter includes every matching event, including featured ones.
        if ($featured->isNotEmpty()) $query->whereNotIn('id', $featured->modelKeys());
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
        match ($filters['sort'] ?? 'soonest') {
            'popular' => $query->orderByDesc('popularity_score')->orderByDesc('view_count'),
            'latest' => $query->orderByDesc('created_at'),
            default => $query->orderByRaw('CASE WHEN COALESCE(end_at, start_at) >= ? THEN 0 ELSE 1 END', [now()]),
        };
        $events = $query->orderBy('start_at')->orderBy('id')->paginate(config('events.per_page'))->withQueryString()->fragment('explore-events');

        return view('events.index', [
            'events' => $events, 'featured' => $events->currentPage() === 1 ? $featured : collect(),
            'filters' => $filters, 'hasFilters' => $hasFilters,
            'categories' => Category::whereHas('events', fn ($q) => $q->published())->orderBy('name')->get(['id', 'name', 'slug']),
            'cities' => Event::published()->select('city')->distinct()->orderBy('city')->pluck('city'),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->status === 'published', 404);
        $event->load(['category', 'coverMedia', 'galleryMedia']);
        $related = Event::published()->with(['category', 'coverMedia'])->whereKeyNot($event->id)
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
        // RFC 5545: fold long UTF-8 lines without splitting a multibyte character.
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
