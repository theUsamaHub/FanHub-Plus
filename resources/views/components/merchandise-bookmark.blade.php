@props(['item', 'saved' => false])
@auth
    <form method="POST" action="{{ route('public.merchandise.bookmark', $item->slug) }}" class="merch-bookmark-form" data-merch-bookmark data-item="{{ $item->id }}">
        @csrf
        <input type="hidden" name="saved" value="{{ $saved ? '0' : '1' }}">
        <button class="merch-heart" type="submit" aria-pressed="{{ $saved ? 'true' : 'false' }}" aria-label="{{ $saved ? 'Remove '.$item->name.' from bookmarks' : 'Bookmark '.$item->name }}" data-name="{{ $item->name }}"><x-site-icon name="heart" /></button>
    </form>
@else
    <a class="merch-heart" href="{{ route('login') }}" data-bookmark-login aria-label="Log in to bookmark {{ $item->name }}"><x-site-icon name="heart" /></a>
@endauth
