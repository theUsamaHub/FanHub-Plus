<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, Content, UpcomingRelease};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('profile');
        $favorites = $user->favoriteCategories()->with('iconMedia')->withCount(['contents' => fn ($q) => $q->visibleToPublic()])->get();
        $history = ActivityLog::where('user_id', $user->id)->where('event', 'member.viewed')
            ->where('auditable_type', Content::class)->latest('id')->limit(100)->pluck('auditable_id')->unique()->take(4);
        $continue = Content::visibleToPublic()->with(['category', 'media'])->whereIn('id', $history)->get()
            ->sortBy(fn ($item) => $history->search($item->id))->values();
        $recommendations = Content::visibleToPublic()->with(['category', 'media'])
            ->when($favorites->isNotEmpty(), fn ($q) => $q->whereIn('category_id', $favorites->modelKeys()))
            ->whereNotIn('id', $continue->modelKeys())->orderByDesc('popularity_score')->latest('published_at')->limit(4)->get();
        $releases = UpcomingRelease::published()->upcoming()->with(['category', 'imageMedia'])->orderByRaw('release_date IS NULL')->orderBy('release_date')->limit(3)->get();
        $activity = ActivityLog::where('user_id', $user->id)->where('event', 'like', 'member.%')->latest('id')->limit(4)->get();
        $bookmarks = $user->bookmarks()->with('bookmarkable')->latest()->limit(4)->get();
        $stats = ['bookmarks' => $user->bookmarks()->count(), 'favorites' => $favorites->count(),
            'watched' => ActivityLog::where('user_id', $user->id)->where('event', 'member.watched')->where('created_at', '>=', now()->startOfWeek())->count(),
            'releases' => UpcomingRelease::published()->upcoming()->count()];
        return view('user.dashboard', compact('user', 'favorites', 'continue', 'recommendations', 'releases', 'activity', 'bookmarks', 'stats'));
    }
}
