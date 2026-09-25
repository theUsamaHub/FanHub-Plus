<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Media;
use App\Services\CategoryService;
use App\Services\FileUploadService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly FileUploadService $fileService
    ) {}

    public function index(Request $request): View
    {
        $categories = $this->categoryService->getPaginated(
            $request->only(['search']),
            15
        );

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = collect($request->validated())
            ->except(['icon', 'remove_icon'])
            ->toArray();

        $category = $this->categoryService->create($data);

        if ($request->hasFile('icon')) {
            try {
                $media = $this->fileService->upload($request->file('icon'), 'uploads/categories');
                $category->update(['icon_media_id' => $media->id]);
            } catch (\RuntimeException $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['icon' => __('Icon upload failed. Please try again.')]);
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): View
    {
        $category->load(['iconMedia'])
            ->loadCount(['contents', 'characterProfiles', 'merchandiseItems', 'events']);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        $category->load('iconMedia');

        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $oldMediaId = $category->icon_media_id;

        $data = collect($request->validated())
            ->except(['icon', 'remove_icon'])
            ->toArray();

        if ($request->boolean('remove_icon')) {
            $data['icon_media_id'] = null;
        }

        $this->categoryService->update($category, $data);

        if ($request->hasFile('icon')) {
            try {
                $media = $this->fileService->upload($request->file('icon'), 'uploads/categories');
                $category->update(['icon_media_id' => $media->id]);
            } catch (\RuntimeException $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['icon' => __('Icon upload failed. Please try again.')]);
            }

            if ($oldMediaId && $oldMediaId !== $media->id) {
                $oldMedia = Media::find($oldMediaId);
                if ($oldMedia && ! $oldMedia->isReferenced()) {
                    $this->fileService->delete($oldMedia);
                }
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->delete($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category moved to trash.');
    }

    public function trashed(): View
    {
        $categories = Category::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('admin.categories.trashed', compact('categories'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->categoryService->restore($id);

        return redirect()->route('admin.categories.trashed')
            ->with('success', 'Category restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        try {
            $this->categoryService->forceDelete($id);
        } catch (QueryException) {
            return redirect()->route('admin.categories.trashed')
                ->with('error', 'This category cannot be permanently deleted because content, characters, or merchandise still reference it.');
        }

        return redirect()->route('admin.categories.trashed')
            ->with('success', 'Category permanently deleted.');
    }
}
