<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Bookmark, Category, Content, Rating, Review};
use App\Services\MemberLibrary;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function bookmark(Request $request, string $type, int $id, MemberLibrary $library)
    {
        $target = $library->resolve($type, $id);
        $data = $request->validate(['saved' => 'required|boolean']);
        $key = ['bookmarkable_type' => $target->getMorphClass(), 'bookmarkable_id' => $target->id];
        if ($data['saved']) {
            $bookmark = $request->user()->bookmarks()->firstOrCreate($key);
            if ($bookmark->wasRecentlyCreated) $library->activity('saved', $target);
        } else $request->user()->bookmarks()->where($key)->delete();
        return back()->with('success', $data['saved'] ? 'Saved to your bookmarks.' : 'Bookmark removed.');
    }

    public function note(Request $request, Bookmark $bookmark)
    {
        abort_unless($bookmark->user_id === $request->user()->id, 403);
        $bookmark->update($request->validate(['note' => 'nullable|string|max:2000']));
        return back()->with('success', 'Private note saved.');
    }

    public function removeBookmark(Request $request, Bookmark $bookmark)
    {
        abort_unless($bookmark->user_id === $request->user()->id, 403);
        $bookmark->delete();
        return back()->with('success', 'Bookmark removed.');
    }

    public function favorite(Request $request, Category $category, MemberLibrary $library)
    {
        $data = $request->validate(['saved' => 'required|boolean']);
        if ($data['saved']) {
            $changes = $request->user()->favoriteCategories()->syncWithoutDetaching([$category->id]);
            if ($changes['attached']) $library->activity('favorited', $category);
        } else $request->user()->favoriteCategories()->detach($category);
        return back()->with('success', $data['saved'] ? 'Added to your favorite fandoms.' : 'Favorite removed.');
    }

    public function rating(Request $request, string $type, int $id, MemberLibrary $library)
    {
        $target = $library->resolve($type, $id);
        $data = $request->validate(['stars' => 'required|integer|between:1,5']);
        $request->user()->ratings()->updateOrCreate(['rateable_type' => $target->getMorphClass(), 'rateable_id' => $target->id],
            ['rating_type' => 'star', 'stars' => $data['stars'], 'is_thumbs_up' => null]);
        $library->activity('rated', $target);
        return back()->with('success', 'Your rating has been saved.');
    }

    public function review(Request $request, string $type, int $id, MemberLibrary $library)
    {
        $target = $library->resolve($type, $id);
        $data = $request->validate(['title' => 'nullable|string|max:150', 'body' => 'required|string|min:10|max:5000']);
        $request->user()->reviews()->updateOrCreate(['reviewable_type' => $target->getMorphClass(), 'reviewable_id' => $target->id],
            [...$data, 'status' => 'pending']);
        $library->activity('reviewed', $target);
        return back()->with('success', 'Review submitted. It will appear after moderator approval.');
    }

    public function removeReview(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $review->delete();
        return back()->with('success', 'Review removed.');
    }

    public function watched(Request $request, Content $content, MemberLibrary $library)
    {
        $library->resolve('content', $content->id);
        abort_unless(in_array($content->type, ['video', 'audio']), 404);
        $data = $request->validate(['watched' => 'required|boolean']);
        $key = ['user_id' => $request->user()->id, 'event' => 'member.watched', 'auditable_type' => $content->getMorphClass(), 'auditable_id' => $content->id];
        if ($data['watched']) \App\Models\ActivityLog::firstOrCreate($key, ['new_values' => ['label' => $content->title]]);
        else \App\Models\ActivityLog::where($key)->delete();
        return back()->with('success', $data['watched'] ? 'Added to your watched history.' : 'Removed from watched history.');
    }
}
