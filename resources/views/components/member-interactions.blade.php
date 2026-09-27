<section class="member-interactions community" id="community" aria-label="Save, rate and review">
    @include('user.partials.messages')
    <header><h2>Make it part of your story.</h2><div class="member-actions">
        @auth<form method="post" action="{{ route('user.bookmark', [$type, $item->id]) }}">@csrf<input type="hidden" name="saved" value="{{ $saved ? 0 : 1 }}"><button class="member-button member-button--quiet" aria-pressed="{{ $saved ? 'true' : 'false' }}"><i class="bi bi-bookmark{{ $saved ? '-fill' : '' }}" aria-hidden="true"></i>{{ $saved ? 'Saved' : 'Save bookmark' }}</button></form>
        @if($type === 'content' && in_array($item->type, ['video', 'audio']))<form method="post" action="{{ route('user.watched', $item) }}">@csrf<input type="hidden" name="watched" value="{{ $watched ? 0 : 1 }}"><button class="member-button member-button--quiet">{{ $watched ? '✓ Watched / listened' : 'Mark watched / listened' }}</button></form>@endif
        @else<a class="member-button member-button--quiet" href="{{ route('login') }}">Sign in to save</a>@endauth
        <button type="button" class="member-button member-button--quiet" data-share-url="{{ url()->current() }}" data-share-title="{{ $item->title ?? $item->name }}"><i class="bi bi-share" aria-hidden="true"></i>Share</button><span class="member-share-status" data-share-status role="status"></span>
    </div></header>
    <div class="community__grid">
        <div class="community__feed">
            <div class="community__heading"><div><span class="community__eyebrow">FAN PERSPECTIVES</span><h2>Ratings &amp; reviews</h2></div><span class="community__count">{{ $reviews->total() }} {{ Str::plural('review', $reviews->total()) }}</span></div>
            <div class="community__summary">
                <strong>{{ $ratingCount ? number_format($average, 1) : '—' }}<small> / 5</small></strong>
                <div><div class="community__stars" aria-hidden="true">@for($star = 1; $star <= 5; $star++)<i class="bi {{ $average >= $star ? 'bi-star-fill' : ($average >= $star - 0.5 ? 'bi-star-half' : 'bi-star') }}"></i>@endfor</div><p>{{ $ratingCount }} {{ Str::plural('star rating', $ratingCount) }}@unless($ratingCount) · Be the first @endunless</p></div>
            </div>
            @if($myReview && $myReview->status !== 'approved')
                <article class="community__review community__review--private">
                    <div class="community__review-meta"><strong>Your review</strong><span class="community__badge">{{ $myReview->status === 'pending' ? 'Awaiting approval' : 'Not published' }}</span></div>
                    <p class="community__hint">{{ $myReview->status === 'pending' ? 'Saved successfully. Only you can see this until a moderator approves it.' : 'This review was not approved. You can edit it and submit it again.' }}</p>
                    @if($myReview->title)<h3>{{ $myReview->title }}</h3>@endif
                    <p class="community__body">{{ $myReview->body }}</p>
                </article>
            @endif
            @forelse($reviews as $review)
                @php($reviewer = $review->user?->profile?->display_name ?: ($review->user?->name ?? 'Former member'))
                <article class="community__review">
                    <div class="community__review-meta">
                        <span class="community__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($reviewer, 0, 1)) }}</span>
                        <div><strong>{{ $reviewer }}</strong><time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('M j, Y') }}</time></div>
                        @if($reviewStars = $reviewRatings->get($review->user_id))<span class="community__review-score" aria-label="{{ $reviewStars }} out of 5 stars"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $reviewStars }}/5</span>@endif
                    </div>
                    @if($review->title)<h3>{{ $review->title }}</h3>@endif
                    <p class="community__body">{{ $review->body }}</p>
                </article>
            @empty
                <div class="community__empty"><i class="bi bi-chat-square-heart" aria-hidden="true"></i><h3>No published reviews yet</h3><p>Share your perspective and start the conversation.</p></div>
            @endforelse
            @include('user.partials.pagination', ['paginator' => $reviews])
        </div>
        <aside class="community__compose" aria-label="Your rating and review">
            @auth
                <h3>Your perspective matters</h3><p class="community__hint">Tell other fans what you think.</p>
                <form class="member-form community__rating-form" method="post" action="{{ route('user.rating', [$type, $item->id]) }}">
                    @csrf
                    <fieldset><legend>Your rating</legend><div class="community__rating-options">
                        @foreach([1, 2, 3, 4, 5] as $value)<label><input type="radio" name="stars" value="{{ $value }}" required @checked((int) old('stars', $mine?->stars) === $value)><span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $value }}</span><span class="community__sr">{{ Str::plural('star', $value) }}</span></label>@endforeach
                    </div></fieldset>
                    <button class="member-button">{{ $mine ? 'Update rating' : 'Save rating' }}</button>
                </form>
                <details class="community__review-form" @if(old('body') || ($myReview && $myReview->status !== 'approved')) open @endif>
                    <summary>{{ $myReview ? 'Edit your review' : 'Write a review' }}</summary>
                    <form class="member-form" method="post" action="{{ route('user.review', [$type, $item->id]) }}">
                        @csrf
                        <label>Title (optional)<input name="title" maxlength="150" value="{{ old('title', $myReview?->title) }}" placeholder="Sum up your experience"></label>
                        <label>Your review<textarea name="body" required minlength="10" maxlength="5000" rows="5" placeholder="What stood out to you?">{{ old('body', $myReview?->body) }}</textarea></label>
                        <p class="community__hint">Reviews appear publicly after approval. Editing a review sends it for approval again.</p>
                        <button class="member-button">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
                    </form>
                </details>
            @else
                <i class="bi bi-chat-heart community__join-icon" aria-hidden="true"></i><h3>Join the conversation</h3>
                <p class="community__hint">Sign in to share a rating or review with fellow fans.</p><a class="member-button" href="{{ route('login') }}">Sign in to review <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            @endauth
        </aside>
    </div>
</section>
