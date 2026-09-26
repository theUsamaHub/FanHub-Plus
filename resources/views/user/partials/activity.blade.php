<ol class="member-timeline">
@forelse($items as $item)
    @php($action = str_replace('member.', '', $item->event))
    <li><span class="member-timeline__icon"><i class="bi bi-{{ match($action) { 'saved' => 'bookmark-fill', 'favorited' => 'heart-fill', 'watched' => 'play-fill', 'rated' => 'star-fill', 'submitted' => 'pencil', default => 'clock' } }}" aria-hidden="true"></i></span><div><strong>{{ ucfirst($action) }}</strong><p>{{ $item->new_values['label'] ?? 'Your account' }}</p></div><time datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->diffForHumans() }}</time></li>
@empty<li class="member-empty">Your story is just beginning. Activity will appear as you explore and save favorites.</li>@endforelse
</ol>
