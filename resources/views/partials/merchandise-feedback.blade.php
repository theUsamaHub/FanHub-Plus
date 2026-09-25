<p class="merch-toast" data-bookmark-status role="status" aria-live="polite" hidden></p>
@guest
<dialog class="merch-login-dialog" data-merch-login aria-labelledby="merch-login-title">
    <button type="button" class="merch-dialog-close" data-close-merch-login aria-label="Close login prompt"><x-site-icon name="close" /></button>
    <x-site-icon name="heart" />
    <h2 id="merch-login-title">Keep your favourites close.</h2>
    <p>Log in to save merchandise to your bookmarks and find it again anytime.</p>
    <a class="join-button" href="{{ route('login') }}">Log in <x-site-icon name="arrow" /></a>
    <a class="merch-dialog-register" href="{{ route('register') }}">New here? Join FanHub Plus</a>
</dialog>
@endguest
