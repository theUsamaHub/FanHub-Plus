<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NearbyEventsController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->isMethod('post')) {
            return app(EventController::class)->index($request, true);
        }

        // Preserve existing links to the standalone discovery page.
        $filters = $request->validate([
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'radius' => ['sometimes', 'integer', Rule::in(config('events.nearby_radii'))],
            'page' => 'nullable|integer|min:1',
        ]);
        $query = Event::published()->with(['category', 'coverMedia'])
            ->whereRaw('COALESCE(end_at, start_at) >= ?', [now()]);
        if (isset($filters['latitude'], $filters['longitude'])) {
            $query->withinRadius((float) $filters['latitude'], (float) $filters['longitude'], (int) ($filters['radius'] ?? 5))
                ->orderBy('distance_km');
        } else {
            $query->whereRaw('1 = 0');
        }
        $events = $query->orderBy('id')->paginate(config('events.per_page'))->withQueryString();
        $events->through(fn ($event) => ['event' => $event, 'distance' => round($event->distance_km, 1)]);

        return response()->view('events.nearby', compact('events', 'filters'))
            ->header('Cache-Control', 'no-store, private');
    }
}
