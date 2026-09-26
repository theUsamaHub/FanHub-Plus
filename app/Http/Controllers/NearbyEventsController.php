<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NearbyEventsController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate(['latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180', 'radius' => 'nullable|integer|between:5,500', 'page' => 'nullable|integer|min:1']);
        $matches = collect();
        if (isset($filters['latitude'], $filters['longitude'])) {
            $latitude = (float) $filters['latitude'];
            $longitude = (float) $filters['longitude'];
            $radius = (int) ($filters['radius'] ?? 100);
            $margin = $radius / 110.5;
            // Bound the candidate query before measuring exact great-circle distance.
            foreach (Event::published()->whereRaw('COALESCE(end_at, start_at) >= ?', [now()])
                ->whereBetween('latitude', [max(-90, $latitude - $margin), min(90, $latitude + $margin)])
                ->whereNotNull('longitude')->select(['id', 'latitude', 'longitude'])->cursor() as $event) {
                $a = sin(deg2rad((float) $event->latitude - $latitude) / 2) ** 2
                    + cos(deg2rad($latitude)) * cos(deg2rad((float) $event->latitude)) * sin(deg2rad((float) $event->longitude - $longitude) / 2) ** 2;
                $distance = 6371 * 2 * asin(sqrt(min(1, max(0, $a))));
                if ($distance <= $radius) $matches->push(['id' => $event->id, 'distance' => round($distance, 1)]);
            }
        }
        $matches = $matches->sortBy('distance')->values();
        $page = max(1, (int) $request->query('page', 1));
        $slice = $matches->forPage($page, 12);
        $records = Event::with(['category', 'coverMedia'])->whereIn('id', $slice->pluck('id'))->get()->keyBy('id');
        $events = new LengthAwarePaginator($slice->map(fn ($match) => ['event' => $records[$match['id']], 'distance' => $match['distance']])->values(), $matches->count(), 12, $page,
            ['path' => route('events.nearby'), 'query' => $request->query()]);
        return view('events.nearby', compact('events', 'filters'));
    }
}
