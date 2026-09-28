<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelationLookupController extends Controller
{
    public function contentsByCategory(Category $category): JsonResponse
    {
        $contents = Content::where('category_id', $category->id)
            ->orderBy('title')
            ->get(['id', 'title', 'type', 'status']);

        return response()->json([
            'category_id' => $category->id,
            'contents' => $contents->map(fn (Content $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'type' => $c->type,
                'status' => $c->status,
            ])->all(),
        ]);
    }

    public function charactersByContent(Content $content): JsonResponse
    {
        $characters = CharacterProfile::with('category')
            ->whereHas('contents', fn ($q) => $q->whereKey($content->id))
            ->orderBy('name')
            ->get(['id', 'name', 'category_id']);

        return response()->json([
            'content_id' => $content->id,
            'category_id' => $content->category_id,
            'characters' => $characters->map(fn (CharacterProfile $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'category_id' => $c->category_id,
            ])->all(),
        ]);
    }

    public function charactersByCategory(Request $request, Category $category): JsonResponse
    {
        $characters = CharacterProfile::where('category_id', $category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'category_id' => $category->id,
            'characters' => $characters->map(fn (CharacterProfile $c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->all(),
        ]);
    }
}