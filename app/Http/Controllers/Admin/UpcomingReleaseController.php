<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpcomingReleaseRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\UpcomingRelease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UpcomingReleaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = UpcomingRelease::with(['category:id,name,slug', 'imageMedia']);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('release_label', 'like', "%{$search}%"));
        }
        if ($request->filled('kind') && in_array($request->input('kind'), UpcomingRelease::KINDS, true)) {
            $query->where('kind', $request->input('kind'));
        }
        if ($request->has('published') && $request->input('published') !== '') {
            $query->where('is_published', $request->boolean('published'));
        }

        $releases = $query->orderBy('sort_order')->orderByRaw('CASE WHEN release_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('release_date')->orderByDesc('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => UpcomingRelease::count(),
            'published' => UpcomingRelease::where('is_published', true)->count(),
            'upcoming' => UpcomingRelease::published()->upcoming()->count(),
            'events' => UpcomingRelease::where('kind', 'event')->count(),
        ];

        return view('admin.upcoming-releases.index', compact('releases', 'stats'));
    }

    public function create(): View
    {
        return view('admin.upcoming-releases.create', $this->formData());
    }

    public function store(UpcomingReleaseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published', true);

        UpcomingRelease::create($data);

        return redirect()->route('admin.upcoming-releases.index')
            ->with('success', 'Upcoming release created successfully.');
    }

    public function edit(UpcomingRelease $upcoming_release): View
    {
        return view('admin.upcoming-releases.edit', ['release' => $upcoming_release] + $this->formData());
    }

    public function update(UpcomingReleaseRequest $request, UpcomingRelease $upcoming_release): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published');

        $upcoming_release->update($data);

        return redirect()->route('admin.upcoming-releases.index')
            ->with('success', 'Upcoming release updated successfully.');
    }

    public function destroy(UpcomingRelease $upcoming_release): RedirectResponse
    {
        $upcoming_release->delete();

        return redirect()->route('admin.upcoming-releases.index')
            ->with('success', 'Upcoming release deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'images' => Media::where('media_type', 'image')
                ->orderByDesc('id')->limit(200)->get(['id', 'original_filename']),
        ];
    }
}
