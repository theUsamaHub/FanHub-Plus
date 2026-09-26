@props(['item', 'type'])
@php
    $target = ['reviewable_type' => $item->getMorphClass(), 'reviewable_id' => $item->id];
    $reviews = \App\Models\Review::where($target)->approved()->with('user.profile')->latest()->paginate(5, ['*'], 'reviews_page')->fragment('community');
    $ratings = \App\Models\Rating::where(['rateable_type' => $item->getMorphClass(), 'rateable_id' => $item->id]);
    $ratingCount = (clone $ratings)->count();
    $average = (clone $ratings)->avg('stars');
    $mine = auth()->check() ? (clone $ratings)->where('user_id', auth()->id())->first() : null;
    $myReview = auth()->check() ? auth()->user()->reviews()->where($target)->first() : null;
    $saved = auth()->check() && auth()->user()->bookmarks()->where(['bookmarkable_type' => $item->getMorphClass(), 'bookmarkable_id' => $item->id])->exists();
    $watched = auth()->check() && $type === 'content' && \App\Models\ActivityLog::where(['user_id' => auth()->id(), 'event' => 'member.watched', 'auditable_type' => $item->getMorphClass(), 'auditable_id' => $item->id])->exists();
@endphp
<section class="member-interactions" id="community" aria-label="Save, rate and review">
    @include('user.partials.messages')
    <header><h2>Make it part of your story.</h2><div class="member-actions">
        @auth<form method="post" action="{{ route('user.bookmark', [$type, $item->id]) }}">@csrf<input type="hidden" name="saved" value="{{ $saved ? 0 : 1 }}"><button class="member-button member-button--quiet" aria-pressed="{{ $saved ? 'true' : 'false' }}"><i class="bi bi-bookmark{{ $saved ? '-fill' : '' }}" aria-hidden="true"></i>{{ $saved ? 'Saved' : 'Save bookmark' }}</button></form>
        @if($type === 'content' && in_array($item->type, ['video', 'audio']))<form method="post" action="{{ route('user.watched', $item) }}">@csrf<input type="hidden" name="watched" value="{{ $watched ? 0 : 1 }}"><button class="member-button member-button--quiet">{{ $watched ? '✓ Watched / listened' : 'Mark watched / listened' }}</button></form>@endif
        @else<a class="member-button member-button--quiet" href="{{ route('login') }}">Sign in to save</a>@endauth
        <button type="button" class="member-button member-button--quiet" data-share-url="{{ url()->current() }}" data-share-title="{{ $item->title ?? $item->name }}"><i class="bi bi-share" aria-hidden="true"></i>Share</button><span class="member-share-status" data-share-status role="status"></span>
    </div></header>
    <div class="member-interactions__grid"><div><h2>From the community</h2><p class="member-interactions__rating">@if($ratingCount)★ {{ number_format($average, 1) }} / 5 · {{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }}@else No ratings yet. Be the first to leave one.@endif</p>
        @forelse($reviews as $review)<article class="member-review"><strong>{{ $review->user?->profile?->display_name ?: ($review->user?->name ?? 'Former member') }}</strong> <small>{{ $review->created_at->format('M j, Y') }}</small>@if($review->title)<h3>{{ $review->title }}</h3>@endif<p>{{ $review->body }}</p></article>@empty<p class="member-muted">There’s room for your perspective here.</p>@endforelse
        @include('user.partials.pagination', ['paginator' => $reviews])
    </div><div>@auth
        <form class="member-form" method="post" action="{{ route('user.rating', [$type, $item->id]) }}">@csrf<label>Your rating<select name="stars" required><option value="">Choose a rating</option>@foreach([5 => '5 — Loved it', 4 => '4 — Really good', 3 => '3 — Good', 2 => '2 — Could be better', 1 => '1 — Not for me'] as $value => $label)<option value="{{ $value }}" @selected($mine?->stars === $value)>{{ $label }}</option>@endforeach</select></label><button class="member-button">{{ $mine ? 'Update rating' : 'Save rating' }}</button></form>
        <details @if(old('body')) open @endif><summary>{{ $myReview ? 'Edit your review ('.$myReview->status.')' : 'Write a review' }}</summary><form class="member-form" method="post" action="{{ route('user.review', [$type, $item->id]) }}">@csrf<label>Title (optional)<input name="title" maxlength="150" value="{{ old('title', $myReview?->title) }}"></label><label>Your review<textarea name="body" required minlength="10" maxlength="5000" rows="4">{{ old('body', $myReview?->body) }}</textarea></label><p class="member-muted">Reviews are checked by a moderator before appearing here. Editing a review sends it for review again.</p><button class="member-button member-button--quiet">Submit review</button></form></details>
        @else<p class="member-muted">Join the conversation. Sign in to rate and review this {{ $type }}.</p><a class="member-button" href="{{ route('login') }}">Sign in</a>@endauth
    </div></div>
</section>
