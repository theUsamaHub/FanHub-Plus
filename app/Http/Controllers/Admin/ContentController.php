<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\Tag;
use App\Services\ContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'search', 'category_id', 'type', 'status', 'featured',
            'year', 'tag_id', 'user_submitted', 'sort',
        ]);

        $contents = $this->contentService->getPaginated($filters, 25);

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $tags = Tag::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => Content::count(),
            'published' => Content::where('status', 'published')->count(),
            'draft' => Content::where('status', 'draft')->count(),
            'pending' => Content::where('status', 'pending_review')->count(),
            'featured' => Content::where('is_featured', true)->count(),
        ];

        return view('admin.contents.index', compact('contents', 'categories', 'tags', 'stats', 'filters'));
    }

    public function create(): View
    {
        return view('admin.contents.create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(),
            'mediaOptions' => $this->mediaOptions(),
        ]);
    }

    public function store(ContentRequest $request): RedirectResponse
    {
        $this->contentService->create(
            $this->contentData($request),
            $request->input('tags', []),
            $this->mediaByRole($request)
        );

        return redirect()->route('admin.contents.index')
            ->with('success', 'Content created successfully.');
    }

    public function show(Content $content): View
    {
        $this->contentService->getById($content->id);

        $content->load(['category', 'tags', 'media', 'submittedBy', 'reviewedBy']);

        return view('admin.contents.show', compact('content'));
    }

    public function edit(Content $content): View
    {
        $content->load(['tags', 'media']);

        return view('admin.contents.edit', [
            'content' => $content,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(),
            'mediaOptions' => $this->mediaOptions(),
            'selected' => $this->selectedMedia($content),
        ]);
    }

    public function update(ContentRequest $request, Content $content): RedirectResponse
    {
        $this->contentService->update(
            $content,
            $this->contentData($request),
            $request->input('tags', []),
            $this->mediaByRole($request)
        );

        return redirect()->route('admin.contents.index')
            ->with('success', 'Content updated successfully.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $this->contentService->delete($content);

        return redirect()->route('admin.contents.index')
            ->with('success', 'Content deleted successfully.');
    }

    public function toggleFeatured(Content $content): RedirectResponse
    {
        $this->contentService->toggleFeatured($content);

        return back()->with('success', 'Featured status updated.');
    }

    public function updateStatus(Request $request, Content $content): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:draft,pending_review,published,rejected'],
        ]);

        $this->contentService->setStatus($content, $request->input('status'), auth()->id());

        return back()->with('success', 'Content status updated.');
    }

    private function contentData(ContentRequest $request): array
    {
        $data = $request->validated();
        unset(
            $data['tags'],
            $data['cover_media_id'],
            $data['gallery_media_ids'],
            $data['trailer_media_id'],
            $data['audio_clip_media_id'],
            $data['attachment_media_ids']
        );

        $data['is_featured'] = $request->boolean('is_featured');
        if (! empty($data['published_at'])) {
            $data['published_at'] = \Carbon\Carbon::parse($data['published_at'], config('publishing.timezone'))
                ->setTimezone(config('app.timezone'));
        }

        return $data;
    }

    private function mediaByRole(ContentRequest $request): array
    {
        return array_filter([
            'cover' => array_values(array_filter([$request->input('cover_media_id')])),
            'gallery' => array_values($request->input('gallery_media_ids', []) ?: []),
            'trailer' => array_values(array_filter([$request->input('trailer_media_id')])),
            'audio_clip' => array_values(array_filter([$request->input('audio_clip_media_id')])),
            'attachment' => array_values($request->input('attachment_media_ids', []) ?: []),
        ], fn ($ids) => $ids !== []);
    }

    private function mediaOptions(): array
    {
        return [
            'images' => Media::where('media_type', 'image')->orderBy('original_filename')->get(),
            'videos' => Media::where('media_type', 'video')->orderBy('original_filename')->get(),
            'audio' => Media::where('media_type', 'audio')->orderBy('original_filename')->get(),
            'documents' => Media::where('media_type', 'document')->orderBy('original_filename')->get(),
        ];
    }

    private function selectedMedia(Content $content): array
    {
        $selected = [
            'cover' => null,
            'gallery' => [],
            'trailer' => null,
            'audio_clip' => null,
            'attachment' => [],
        ];

        foreach ($content->media as $media) {
            $role = $media->pivot->role;
            if (in_array($role, ['cover', 'trailer', 'audio_clip'], true)) {
                $selected[$role] = $media->id;
            } elseif (in_array($role, ['gallery', 'attachment'], true)) {
                $selected[$role][] = $media->id;
            }
        }

        return $selected;
    }
}
