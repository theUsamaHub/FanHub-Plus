const hero = document.querySelector('[data-video-hero]');

if (hero) {
    const video = hero.querySelector('video');
    const button = hero.querySelector('.fan-hero__playback');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let wantsPlayback = !reducedMotion.matches && !navigator.connection?.saveData;
    let visible = true;
    const updateButton = () => {
        const playing = !video.paused;
        button.setAttribute('aria-label', playing ? 'Pause background video' : 'Play background video');
        button.setAttribute('aria-pressed', String(playing));
        button.querySelector('[data-video-symbol]').textContent = playing ? 'Ⅱ' : '▶';
        button.querySelector('[data-video-label]').textContent = playing ? 'Pause motion' : 'Play motion';
    };
    const syncPlayback = () => {
        if (wantsPlayback && visible && !document.hidden) video.play().catch(updateButton);
        else video.pause();
    };
    button.hidden = false;
    button.addEventListener('click', () => { wantsPlayback = video.paused; syncPlayback(); });
    video.addEventListener('play', updateButton);
    video.addEventListener('pause', updateButton);
    video.addEventListener('error', () => { button.hidden = true; });
    video.querySelector('source').addEventListener('error', () => { button.hidden = true; });
    reducedMotion.addEventListener('change', () => { wantsPlayback = !reducedMotion.matches; syncPlayback(); });
    document.addEventListener('visibilitychange', syncPlayback);
    new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; syncPlayback(); }, { threshold: .05 }).observe(hero);
}
