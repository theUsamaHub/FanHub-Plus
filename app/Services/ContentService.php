<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentMedia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContentService
{
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Content::query()->with(['category', 'tags', 'media', 'submittedBy']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('is_featured', (bool) $filters['featured']);
        }

        if (! empty($filters['year'])) {
            $query->whereYear('release_date', $filters['year']);
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
        }

        if (isset($filters['user_submitted']) && $filters['user_submitted'] !== '') {
            $query->where('is_user_submitted', (bool) $filters['user_submitted']);
        }

        match ($filters['sort'] ?? 'latest') {
            'views' => $query->orderByDesc('view_count')->orderByDesc('id'),
            'title' => $query->orderBy('title'),
            'popularity' => $query->orderByDesc('popularity_score')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getById(int $id): Content
    {
        return Content::with(['category', 'tags', 'media', 'submittedBy', 'reviewedBy'])->findOrFail($id);
    }

    public function create(array $data, array $tagIds = [], array $mediaByRole = []): Content
    {
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        $data = $this->applyPublishingDefaults($data);

        $content = Content::create($data);

        $content->tags()->sync($tagIds);
        $this->syncMedia($content, $mediaByRole);

        return $content;
    }

    public function update(Content $content, array $data, array $tagIds = [], array $mediaByRole = []): Content
    {
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        $data = $this->applyPublishingDefaults($data, $content);

        $content->update($data);
        $content->tags()->sync($tagIds);
        $this->syncMedia($content, $mediaByRole);

        return $content->fresh(['category', 'tags', 'media']);
    }

    public function delete(Content $content): bool
    {
        return (bool) $content->delete();
    }

    public function toggleFeatured(Content $content): Content
    {
        $content->update(['is_featured' => ! $content->is_featured]);

        return $content->fresh();
    }

    public function setStatus(Content $content, string $status, ?int $reviewedBy = null): Content
    {
        $data = ['status' => $status];

        if ($status === 'published' && ! $content->published_at) {
            $data['published_at'] = now();
        }

        if ($reviewedBy !== null) {
            $data['reviewed_by'] = $reviewedBy;
        }

        $content->update($data);

        return $content->fresh();
    }

    /**
     * @param  array<string, array<int, int>>  $mediaByRole
     */
    public function syncMedia(Content $content, array $mediaByRole): void
    {
        ContentMedia::where('content_id', $content->id)->delete();

        $sort = 0;
        foreach ($mediaByRole as $role => $mediaIds) {
            foreach ($mediaIds as $mediaId) {
                ContentMedia::create([
                    'content_id' => $content->id,
                    'media_id' => $mediaId,
                    'role' => $role,
                    'sort_order' => $sort++,
                ]);
            }
        }
    }

    public function countByStatus(): Collection
    {
        return Content::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    private function applyPublishingDefaults(array $data, ?Content $existing = null): array
    {
        if (($data['status'] ?? null) === 'published' && empty($data['published_at']) && ! ($existing?->published_at)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
