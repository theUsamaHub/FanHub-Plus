<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, Content};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('profile');
        $favorites = $user->favoriteCategories()->with('iconMedia')->withCount(['contents' => fn ($q) => $q->visibleToPublic()])->orderBy('categories.name')->get();
        $visit = ActivityLog::firstOrCreate([
            'user_id' => $user->id,
            'event' => 'dashboard.visited',
            'auditable_type' => $user->getMorphClass(),
            'auditable_id' => $user->id,
        ], ['subject' => 'Dashboard welcome', 'description' => 'Opened the dashboard for the first time.']);
        $firstVisit = $visit->wasRecentlyCreated;
        $fandomNames = $favorites->take(3)->pluck('name')->join(', ', ' and ');
        if ($favorites->count() > 3) $fandomNames .= ' and your other favorite fandoms';
        $welcome = [
            'greeting' => $firstVisit ? 'Welcome to your fan space,' : 'Welcome back,',
            'message' => $favorites->isEmpty()
                ? ($firstVisit ? 'Make yourself at home. Choose your favorite fandoms to make this space feel like you.'
                    : 'Your next favorite world is waiting. Choose a few fandoms to personalize your discoveries.')
                : ($firstVisit ? "Your love for {$fandomNames} has a home here. Start exploring stories, save your favorites, and find your next obsession."
                    : "Ready for more from {$fandomNames}? Pick up where you left off or discover something new from the worlds you love."),
            'action' => $favorites->isEmpty() ? 'Choose your fandoms' : 'Explore your fandoms',
            'url' => $favorites->isEmpty() || $favorites->count() > 1
                ? route('user.favorites') : route('public.explore', ['category' => $favorites->first()->slug]),
        ];
        $history = ActivityLog::where('user_id', $user->id)->where('event', 'member.viewed')
            ->where('auditable_type', Content::class)->latest('id')->limit(100)->pluck('auditable_id')->unique()->take(4);
        $continue = Content::visibleToPublic()->with(['category', 'media'])->whereIn('id', $history)->get()
            ->sortBy(fn ($item) => $history->search($item->id))->values();
        $recommendations = Content::forUser($user)->visibleToPublic()->with(['category', 'media'])
            ->whereNotIn('id', $continue->modelKeys())->orderByDesc('popularity_score')->latest('published_at')->limit(4)->get();
        $releases = Content::forUser($user)->visibleToPublic()->upcoming()->with(['category', 'media'])->orderByRaw('release_date IS NULL')->orderBy('release_date')->limit(3)->get();
        $activity = ActivityLog::where('user_id', $user->id)->where('event', 'like', 'member.%')->latest('id')->limit(4)->get();
        $bookmarks = $user->bookmarks()->with('bookmarkable')->latest()->limit(4)->get();
        $stats = ['bookmarks' => $user->bookmarks()->count(), 'favorites' => $favorites->count(),
            'watched' => ActivityLog::where('user_id', $user->id)->where('event', 'member.watched')->where('created_at', '>=', now()->startOfWeek())->count(),
            'releases' => Content::visibleToPublic()->upcoming()->count()];
        return view('user.dashboard', compact('user', 'favorites', 'continue', 'recommendations', 'releases', 'activity', 'bookmarks', 'stats', 'welcome'));
    }
}
