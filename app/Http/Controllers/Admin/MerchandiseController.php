<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchandiseRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\MerchandiseItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchandiseController extends Controller
{
    public function index(Request $request): View
    {
        $query = MerchandiseItem::with(['category', 'imageMedia']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($tag = $request->input('tag')) {
            $query->where('tag', $tag);
        }

        if ($request->has('upcoming') && $request->input('upcoming') !== '') {
            $query->where('is_upcoming', (bool) $request->input('upcoming'));
        }

        $items = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.merchandise.index', compact('items', 'categories'));
    }

    public function create(): View
    {
        return view('admin.merchandise.create', $this->formData());
    }

    public function store(MerchandiseRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['is_upcoming']);
        $data['is_upcoming'] = $request->boolean('is_upcoming');

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        MerchandiseItem::create($data);

        return redirect()->route('admin.merchandise.index')
            ->with('success', 'Merchandise created successfully.');
    }

    public function show(MerchandiseItem $merchandise): View
    {
        $merchandise->load(['category', 'imageMedia']);

        return view('admin.merchandise.show', ['item' => $merchandise]);
    }

    public function edit(MerchandiseItem $merchandise): View
    {
        return view('admin.merchandise.edit', array_merge($this->formData(), ['item' => $merchandise]));
    }

    public function update(MerchandiseRequest $request, MerchandiseItem $merchandise): RedirectResponse
    {
        $data = $request->safe()->except(['is_upcoming']);
        $data['is_upcoming'] = $request->boolean('is_upcoming');

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        $merchandise->update($data);

        return redirect()->route('admin.merchandise.index')
            ->with('success', 'Merchandise updated successfully.');
    }

    public function destroy(MerchandiseItem $merchandise): RedirectResponse
    {
        $merchandise->delete();

        return redirect()->route('admin.merchandise.index')
            ->with('success', 'Merchandise deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'images' => Media::where('media_type', 'image')->orderBy('original_filename')->get(),
        ];
    }
}
