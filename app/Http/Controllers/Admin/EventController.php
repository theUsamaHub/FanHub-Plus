<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with(['category', 'coverMedia']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->has('upcoming') && $request->input('upcoming') !== '') {
            if ($request->boolean('upcoming')) {
                $query->upcoming();
            } else {
                $query->where('start_at', '<', now());
            }
        }

        $events = $query->orderBy('start_at')->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.events.index', compact('events', 'categories'));
    }

    public function create(): View
    {
        return view('admin.events.create', $this->formData());
    }

    public function store(EventRequest $request): RedirectResponse
    {
        Event::create($request->validated());

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load(['category', 'coverMedia']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        return array_merge($this->formData(), ['event' => $event]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'images' => Media::where('media_type', 'image')->orderBy('original_filename')->get(),
        ];
    }
}
