<?php

namespace App\Http\Controllers;

use App\Models\{Category, Content};
use Illuminate\Http\Request;
use Illuminate\View\View;

class FanContentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:article,image,video,audio'],
        ]);
        $query = Content::visibleToPublic()->where('is_user_submitted', true)
            ->with(['category', 'media', 'submittedBy.profile']);
        if ($term = trim($filters['q'] ?? '')) {
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])
                ->orWhereRaw("LOWER(excerpt) LIKE ? ESCAPE '!'", [$term]));
        }
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }
        if (! empty($filters['type'])) $query->ofType($filters['type']);

        return view('public.fan-content.index', [
            'contents' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString(),
            'categories' => Category::whereHas('contents', fn ($q) => $q->visibleToPublic()->where('is_user_submitted', true))->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Content $content): View
    {
        abort_unless($content->is_user_submitted && Content::visibleToPublic()->whereKey($content->id)->exists(), 404);
        $content->load(['category', 'media', 'submittedBy.profile']);
        $content->recordView();

        return view($request->ajax() ? 'public.fan-content.detail' : 'public.fan-content.show', compact('content'));
    }
}
