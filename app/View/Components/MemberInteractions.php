<?php

namespace App\View\Components;

use App\Models\{ActivityLog, Rating, Review};
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\View\View;

class MemberInteractions extends Component
{
    public function __construct(public Model $item, public string $type) {}

    public function render(): View
    {
        $user = auth()->user();
        $target = ['reviewable_type' => $this->item->getMorphClass(), 'reviewable_id' => $this->item->id];
        $reviews = Review::where($target)->approved()->with('user.profile')
            ->latest()->orderByDesc('id')->paginate(5, ['*'], 'reviews_page')->withQueryString()->fragment('community');
        $ratings = Rating::where(['rateable_type' => $this->item->getMorphClass(), 'rateable_id' => $this->item->id])
            ->where('rating_type', 'star')->whereBetween('stars', [1, 5]);
        $summary = (clone $ratings)->selectRaw('COUNT(*) as total, AVG(stars) as average')->first();
        $ratingCount = (int) $summary->total;
        $average = (float) $summary->average;
        $reviewRatings = (clone $ratings)->whereIn('user_id', $reviews->pluck('user_id'))->pluck('stars', 'user_id');
        $mine = $user ? (clone $ratings)->where('user_id', $user->id)->first() : null;
        $myReview = $user?->reviews()->where($target)->first();
        $saved = $user && $user->bookmarks()->where(['bookmarkable_type' => $this->item->getMorphClass(), 'bookmarkable_id' => $this->item->id])->exists();
        $watched = $user && $this->type === 'content' && ActivityLog::where(['user_id' => $user->id, 'event' => 'member.watched',
            'auditable_type' => $this->item->getMorphClass(), 'auditable_id' => $this->item->id])->exists();

        return view('components.member-interactions', compact('reviews', 'ratingCount', 'average', 'reviewRatings', 'mine', 'myReview', 'saved', 'watched'));
    }
}
