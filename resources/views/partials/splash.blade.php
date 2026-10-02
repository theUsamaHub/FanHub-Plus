{{-- Critical styles and controller stay inline so the intro never waits for the app bundle. --}}
<style>
    #splash-screen { position:fixed; inset:0; z-index:9999; display:grid; place-items:center; overflow:hidden; background:radial-gradient(ellipse at 50% 40%, #342034 0%, #100c18 55%, #080810 100%); color:#fff9f2; opacity:1; transition:opacity .25s ease; }
    #splash-screen[hidden] { display:none; }
    #splash-screen .splash-brand { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; gap:28px; padding:32px 20px; text-align:center; transition:opacity .25s ease; }
    #splash-screen .fh-brand { display:flex; align-items:center; gap:14px; text-decoration:none; color:#fff9f2 !important; }
    #splash-screen .fh-brand-mark { width:64px; height:70px; color:#fff9f2; filter:drop-shadow(0 0 24px #ff951b33); }
    #splash-screen .fh-brand-wordmark { font:800 clamp(28px, 8vw, 38px)/1 Arial,sans-serif; letter-spacing:-1.5px; }
    #splash-screen .fh-brand-wordmark > span { color:#fca129; }
    #splash-screen .fh-brand-wordmark small { display:block; margin-top:10px; font:600 7px/1.5 Arial,sans-serif; letter-spacing:1.3px; color:#c2b4ce; }
    #splash-screen .splash-welcome { margin:0; color:#c2b4ce; font:400 13px/1.6 Arial,sans-serif; letter-spacing:.4px; }
    #splash-screen .splash-track { width:100px; height:3px; overflow:hidden; border-radius:9px; background:#ffffff15; }
    #splash-screen .splash-track::after { content:''; display:block; width:45%; height:100%; border-radius:inherit; background:linear-gradient(90deg,#ff922e,#ffd65a); animation:splashTravel 1.2s ease-in-out infinite alternate; }
    #splash-video { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .25s ease; }
    #splash-screen.is-playing #splash-video { opacity:1; }
    #splash-screen.splash-desktop .splash-brand { display:none; }
    #splash-screen.splash-desktop { background:transparent; }
    #splash-screen.splash-desktop:not(.is-playing) { pointer-events:none; }
    #splash-screen.splash-desktop:not(.is-playing) #splash-skip { visibility:hidden; }
    #splash-skip { position:absolute; top:max(20px,env(safe-area-inset-top)); right:max(20px,env(safe-area-inset-right)); z-index:2; padding:10px 18px; min-height:44px; border:1px solid #ffffff40; border-radius:24px; background:#100c18b3; color:#fff9f2; font:600 13px Arial,sans-serif; cursor:pointer; }
    #splash-skip:focus-visible { outline:2px solid #ffd65a; outline-offset:4px; }
    @keyframes splashTravel { from { transform:translateX(0); } to { transform:translateX(125%); } }
    @media(max-width:700px) { #splash-video { display:none; } }
    @media(prefers-reduced-motion:reduce) { #splash-screen, #splash-video, #splash-screen .splash-brand { transition:none; } #splash-screen .splash-track::after { animation:none; width:100%; } }
</style>
<div id="splash-screen" data-splash hidden>
    <div class="splash-brand">
        <x-site-brand tabindex="-1" />
        <p class="splash-welcome">Your next discovery awaits.</p>
        <div class="splash-track" aria-hidden="true"></div>
    </div>
    <video id="splash-video" data-src="{{ asset('videos/splash screen video.mp4') }}" muted playsinline preload="none" aria-hidden="true" tabindex="-1"></video>
    <button id="splash-skip" type="button">Skip intro</button>
</div>
<script>
(() => {
    const splash = document.getElementById('splash-screen');
    const video = document.getElementById('splash-video');
    const skip = document.getElementById('splash-skip');
    const storageKey = 'fanhub-splash-shown';
    try {
        if (sessionStorage.getItem(storageKey) === '1') {
            splash.remove();
            return;
        }
    } catch (e) {}

    const mobile = matchMedia('(max-width: 700px)');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let dismissed = false;
    let playbackTimer;
    let deadline;
    function stopVideo() {
        video.pause();
        video.removeAttribute('src');
        video.load();
    }
    function dismiss() {
        if (dismissed) return;
        dismissed = true;
        clearTimeout(deadline);
        clearTimeout(playbackTimer);
        mobile.removeEventListener('change', adaptToDevice);
        reducedMotion.removeEventListener('change', adaptToDevice);
        try { sessionStorage.setItem(storageKey, '1'); } catch (e) {}
        stopVideo();
        splash.style.opacity = '0';
        setTimeout(() => {
            const hadFocus = splash.contains(document.activeElement);
            splash.remove();
            if (hadFocus) document.getElementById('main-content')?.focus({ preventScroll: true });
        }, reducedMotion.matches ? 0 : 250);
    }
    function adaptToDevice() {
        if (!mobile.matches && !reducedMotion.matches) return;
        dismiss();
    }
    splash.hidden = false;
    skip.addEventListener('click', dismiss);
    splash.addEventListener('keydown', event => { if (event.key === 'Escape') dismiss(); });
    mobile.addEventListener('change', adaptToDevice);
    reducedMotion.addEventListener('change', adaptToDevice);
    if (mobile.matches) {
        playbackTimer = setTimeout(dismiss, 1200);
        return;
    }
    splash.classList.add('splash-desktop');
    if (reducedMotion.matches || navigator.connection?.saveData) {
        dismiss();
        return;
    }
    // Keep the page visible while waiting; a late video must never interrupt it.
    deadline = setTimeout(dismiss, 1000);
    video.addEventListener('playing', () => {
        if (dismissed) return;
        clearTimeout(deadline);
        splash.classList.add('is-playing');
        if (!playbackTimer) playbackTimer = setTimeout(dismiss, 2000);
    });
    video.addEventListener('ended', dismiss);
    video.addEventListener('error', dismiss);
    video.src = video.dataset.src;
    video.play().catch(dismiss);
})();
</script>
