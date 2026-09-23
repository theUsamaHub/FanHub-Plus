<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly FileUploadService $fileService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryService->getPaginated(
            $request->only(['search']),
            $request->input('per_page', 15)
        );

        return CategoryResource::collection($categories);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $data = collect($request->validated())
            ->except(['icon', 'remove_icon'])
            ->toArray();

        $category = $this->categoryService->create($data);

        if ($request->hasFile('icon')) {
            $media = $this->fileService->upload($request->file('icon'), 'uploads/categories');
            $category->update(['icon_media_id' => $media->id]);
        }

        return (new CategoryResource($category->fresh(['iconMedia'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $category->load('iconMedia');

        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $data = collect($request->validated())
            ->except(['icon', 'remove_icon'])
            ->toArray();

        if ($request->boolean('remove_icon')) {
            $data['icon_media_id'] = null;
        }

        $this->categoryService->update($category, $data);

        if ($request->hasFile('icon')) {
            $oldMedia = $category->iconMedia;
            $media = $this->fileService->upload($request->file('icon'), 'uploads/categories');
            $category->update(['icon_media_id' => $media->id]);
            if ($oldMedia) {
                $this->fileService->delete($oldMedia);
            }
        }

        return (new CategoryResource($category->fresh(['iconMedia'])))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
