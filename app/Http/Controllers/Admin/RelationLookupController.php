<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lightweight JSON endpoints used by dependent dropdowns in admin forms.
 *   GET /admin/categories/{category}/contents
 *   GET /admin/contents/{content}/characters
 *
 * Both endpoints sit inside the admin middleware stack (see routes/admin.php),
 * so they inherit the existing admin auth / role / ip-restrict protection.
 */
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
        // Only characters actually related to this content through
        // character_contents should appear in the dropdown.
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

    /**
     * Universal "characters by category" used by the Character admin form
     * when adding/removing a multi-select without pre-existing content links.
     * Optional helper: filters characters to the chosen Category only.
     */
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