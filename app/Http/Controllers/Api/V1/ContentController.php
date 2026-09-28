<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function show(Content $content): JsonResponse
    {
        $content->load(['category', 'media', 'tags', 'submittedBy', 'characters.category', 'characters.imageMedia', 'merchandiseItems.category', 'merchandiseItems.imageMedia', 'merchandiseItems.character', 'events.category', 'events.coverMedia']);

        // Increment view count
        $content->increment('view_count');

        return response()->json([
            'id' => $content->id,
            'slug' => $content->slug,
            'title' => $content->title,
            'excerpt' => $content->excerpt,
            'body' => $content->body,
            'kind' => $content->kind,
            'kind_label' => $content->kind_label,
            'release_date' => $content->release_date?->toIso8601String(),
            'release_label' => $content->release_label,
            'reading_minutes' => $content->reading_minutes,
            'view_count' => $content->view_count,
            'popularity_score' => $content->popularity_score,
            'artwork_url' => $content->artwork_url,
            'cover' => $content->cover ? [
                'url' => $content->cover->url,
                'alt_text' => $content->cover->alt_text,
            ] : null,
            'gallery' => $content->media->where('pivot.role', 'gallery')->map(fn ($m) => [
                'url' => $m->url,
                'alt_text' => $m->alt_text,
            ])->values(),
            'trailers' => $content->media->where('pivot.role', 'trailer')->map(fn ($m) => [
                'url' => $m->url,
                'duration' => $m->duration,
                'alt_text' => $m->alt_text,
            ])->values(),
            'audio_clips' => $content->media->where('pivot.role', 'audio')->map(fn ($m) => [
                'url' => $m->url,
                'alt_text' => $m->alt_text,
                'duration' => $m->duration,
                'original_filename' => $m->original_filename,
            ])->values(),
            'category' => $content->category ? [
                'id' => $content->category->id,
                'name' => $content->category->name,
                'slug' => $content->category->slug,
            ] : null,
            'tags' => $content->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])->values(),
            'submitted_by' => $content->submittedBy ? [
                'id' => $content->submittedBy->id,
                'name' => $content->submittedBy->name,
            ] : null,
            'characters' => $content->characters->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'bio' => $c->bio,
                'artwork_url' => $c->artwork_url,
                'image_media' => $c->imageMedia ? [
                    'url' => $c->imageMedia->url,
                    'alt_text' => $c->imageMedia->alt_text,
                ] : null,
            ])->values(),
            'merchandise' => $content->merchandiseItems->map(fn ($m) => [
                'id' => $m->id,
                'slug' => $m->slug,
                'name' => $m->name,
                'artwork_url' => $m->artwork_url,
                'image_media' => $m->imageMedia ? [
                    'url' => $m->imageMedia->url,
                    'alt_text' => $m->imageMedia->alt_text,
                ] : null,
                'character' => $m->character ? [
                    'id' => $m->character->id,
                    'name' => $m->character->name,
                ] : null,
                'display_tag' => $m->display_tag,
            ])->values(),
            'events' => $content->events->where('status', 'published')->whereRaw('COALESCE(end_at, start_at) >= ?', [now()])->map(fn ($e) => [
                'id' => $e->id,
                'slug' => $e->slug,
                'title' => $e->title,
                'start_at' => $e->start_at?->toIso8601String(),
                'end_at' => $e->end_at?->toIso8601String(),
                'city' => $e->city,
                'artwork_url' => $e->artwork_url,
            ])->values(),
        ]);
    }
}