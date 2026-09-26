<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharacterRequest;
use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function index(Request $request): View
    {
        $query = CharacterProfile::with(['category', 'imageMedia'])->withCount('contents');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $characters = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.characters.index', compact('characters', 'categories'));
    }

    public function create(): View
    {
        return view('admin.characters.create', $this->formData([]));
    }

    public function store(CharacterRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $contentIds = $payload['content_ids'] ?? [];
        $data = collect($payload)->except(['content_ids'])->all();

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        $character = DB::transaction(function () use ($data, $contentIds) {
            $character = CharacterProfile::create($data);
            $character->contents()->sync($contentIds);
            return $character;
        });

        return redirect()->route('admin.characters.index')
            ->with('success', 'Character created successfully.');
    }

    public function show(CharacterProfile $character): View
    {
        $character->load(['category', 'imageMedia', 'contents']);

        return view('admin.characters.show', [
            'character' => $character,
            'availableContents' => Content::orderBy('title')->get(['id', 'title', 'type', 'status']),
        ]);
    }

    public function edit(CharacterProfile $character): View
    {
        $character->load('contents');
        $selectedContentIds = $character->contents->pluck('id')->all();

        return view('admin.characters.edit', array_merge(
            $this->formData($selectedContentIds),
            [
                'character' => $character,
                'selectedContentIds' => $selectedContentIds,
            ]
        ));
    }

    public function update(CharacterRequest $request, CharacterProfile $character): RedirectResponse
    {
        $payload = $request->validated();
        $contentIds = $payload['content_ids'] ?? [];
        $data = collect($payload)->except(['content_ids'])->all();

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        DB::transaction(function () use ($character, $data, $contentIds) {
            $character->update($data);
            $character->contents()->sync($contentIds);
        });

        return redirect()->route('admin.characters.index')
            ->with('success', 'Character updated successfully.');
    }

    public function attachContent(Request $request, CharacterProfile $character): RedirectResponse
    {
        $request->validate([
            'content_ids' => ['required', 'array'],
            'content_ids.*' => ['integer', 'exists:contents,id'],
        ]);

        $character->contents()->syncWithoutDetaching($request->input('content_ids', []));

        return back()->with('success', 'Related content attached.');
    }

    public function detachContent(CharacterProfile $character, Content $content): RedirectResponse
    {
        $character->contents()->detach($content->id);

        return back()->with('success', 'Related content removed.');
    }

    public function destroy(CharacterProfile $character): RedirectResponse
    {
        $character->delete();

        return redirect()->route('admin.characters.index')
            ->with('success', 'Character deleted successfully.');
    }

    private function formData(array $selectedContentIds = []): array
    {
        $categoryId = old('category_id');
        // Build the initial Content list scoped to the current Category
        // (preserved across validation failures). The "selected" IDs are
        // always included even if their Category changed so the form can
        // show stale selections on Edit until the admin picks a new
        // Category — the JS layer then reloads from the lookup endpoint.
        $contentsQuery = Content::orderBy('title')->get(['id', 'title', 'type', 'status', 'category_id']);
        $contents = $categoryId
            ? $contentsQuery->where('category_id', $categoryId)
            : $contentsQuery;
        if (! empty($selectedContentIds)) {
            $contents = $contents->merge(
                $contentsQuery->whereIn('id', $selectedContentIds)
            )->unique('id')->values();
        }

        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'images' => Media::where('media_type', 'image')->orderBy('original_filename')->get(),
            'contents' => $contents,
        ];
    }
}
