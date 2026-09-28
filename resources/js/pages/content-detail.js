import Swiper from 'swiper';
import { A11y, EffectCoverflow, Keyboard, Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/pagination';
import '../../css/pages/content-detail.css';

const page = document.querySelector('[data-content-detail]');
if (page) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealControls = (selectors) => page.querySelectorAll(selectors).forEach(button => { button.hidden = false; });
    const characters = page.querySelector('[data-character-coverflow]');
    if (characters) {
        const count = characters.querySelectorAll('.swiper-slide').length;
        const slider = new Swiper(characters, {
            modules: [A11y, EffectCoverflow, Keyboard, Navigation],
            effect: 'coverflow', slidesPerView: 'auto', centeredSlides: true,
            initialSlide: Math.floor((count - 1) / 2), spaceBetween: 16,
            speed: reducedMotion ? 0 : 650, grabCursor: count > 1,
            watchOverflow: false, rewind: count > 1, slideToClickedSlide: true,
            coverflowEffect: { rotate: 12, stretch: 0, depth: 115, modifier: 1, slideShadows: true },
            keyboard: { enabled: true, onlyInViewport: true },
            navigation: { prevEl: page.querySelector('[data-character-prev]'), nextEl: page.querySelector('[data-character-next]') },
            a11y: { containerMessage: 'Characters linked to this content', itemRoleDescriptionMessage: 'Character', slideRole: 'link' },
        });
        // Bring keyboard-focused cards into view without changing the focused link.
        characters.addEventListener('focusin', event => {
            const card = event.target.closest('.swiper-slide');
            if (card) slider.slideTo([...slider.slides].indexOf(card));
        });
        revealControls('[data-character-prev], [data-character-next]');
    }
    const gallery = page.querySelector('[data-gallery-slider]');
    if (gallery) {
        const count = gallery.querySelectorAll('.swiper-slide').length;
        new Swiper(gallery, {
            modules: [A11y, Navigation, Pagination], slidesPerView: Math.min(count, 2.15), spaceBetween: 9,
            speed: reducedMotion ? 0 : 450, watchOverflow: true,
            breakpoints: { 540: { slidesPerView: Math.min(count, 3) }, 1101: { slidesPerView: Math.min(count, 5) } },
            navigation: { prevEl: page.querySelector('[data-gallery-prev]'), nextEl: page.querySelector('[data-gallery-next]') },
            pagination: { el: page.querySelector('[data-gallery-pagination]'), clickable: true },
            a11y: { containerMessage: 'Content image gallery', slideRole: 'link' },
        });
        revealControls('[data-gallery-prev], [data-gallery-next]');
    }
    const merch = page.querySelector('[data-merch-slider]');
    if (merch) {
        new Swiper(merch, {
            modules: [A11y, Navigation], slidesPerView: 2.2, spaceBetween: 12,
            speed: reducedMotion ? 0 : 450, watchOverflow: true,
            breakpoints: { 540: { slidesPerView: 3.2 }, 800: { slidesPerView: 4 }, 1101: { slidesPerView: 7.5 } },
            navigation: { prevEl: page.querySelector('[data-merch-prev]'), nextEl: page.querySelector('[data-merch-next]') },
            a11y: { containerMessage: 'Merchandise linked to this content' },
        });
        revealControls('[data-merch-prev], [data-merch-next]');
    }

    const dialog = page.querySelector('[data-gallery-dialog]');
    let galleryTrigger;
    page.querySelectorAll('[data-gallery-image]').forEach(link => {
        link.addEventListener('click', event => {
            // Swiper suppresses click after a drag; a regular click opens the full image.
            if (gallery.swiper && !gallery.swiper.allowClick) return;
            event.preventDefault();
            galleryTrigger = link;
            const image = link.querySelector('img');
            dialog.querySelector('[data-lightbox-image]').src = link.href;
            dialog.querySelector('[data-lightbox-image]').alt = image.alt;
            dialog.querySelector('[data-lightbox-caption]').textContent = image.alt;
            dialog.showModal();
        });
    });
    // The requested interaction closes on any click, including the enlarged image.
    dialog.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => galleryTrigger?.focus({ preventScroll: true }));
    dialog.querySelector('[data-lightbox-image]').addEventListener('error', () => {
        dialog.querySelector('[data-lightbox-caption]').textContent = 'This image is currently unavailable. Click anywhere to close.';
    });

    const pauseOthers = current => page.querySelectorAll('audio, video').forEach(media => {
        if (media !== current) media.pause();
    });
    page.querySelectorAll('[data-trailer]').forEach(container => {
        const video = container.querySelector('video');
        const cover = container.querySelector('[data-trailer-play]');
        cover.hidden = false;
        video.controls = false;
        cover.addEventListener('click', async () => {
            cover.hidden = true;
            video.controls = true;
            video.focus();
            try { await video.play(); } catch { /* Native controls remain available on playback failure. */ }
        });
        video.addEventListener('play', () => { cover.hidden = true; pauseOthers(video); });
        video.addEventListener('ended', () => { cover.hidden = false; video.controls = false; });
    });
    page.querySelectorAll('[data-audio-player]').forEach(container => {
        const audio = container.querySelector('audio');
        const controls = container.querySelector('.cd-audio-controls');
        const toggle = container.querySelector('[data-audio-toggle]');
        const seek = container.querySelector('[data-audio-seek]');
        const time = container.querySelector('[data-audio-time]');
        const formatTime = value => `${Math.floor((value || 0) / 60)}:${String(Math.floor((value || 0) % 60)).padStart(2, '0')}`;
        const update = () => {
            const ready = Number.isFinite(audio.duration) && audio.duration > 0;
            seek.disabled = !ready;
            seek.value = ready ? audio.currentTime / audio.duration * 100 : 0;
            time.textContent = `${formatTime(audio.currentTime)} / ${formatTime(ready ? audio.duration : 0)}`;
        };
        audio.hidden = true;
        controls.hidden = false;
        toggle.addEventListener('click', async () => {
            if (!audio.paused) { audio.pause(); return; }
            try { await audio.play(); } catch { audio.hidden = false; controls.hidden = true; }
        });
        audio.addEventListener('play', () => {
            pauseOthers(audio);
            toggle.setAttribute('aria-label', 'Pause audio');
            toggle.querySelector('i').className = 'bi bi-pause-fill';
        });
        audio.addEventListener('pause', () => {
            toggle.setAttribute('aria-label', 'Play audio');
            toggle.querySelector('i').className = 'bi bi-play-fill';
        });
        ['loadedmetadata', 'durationchange', 'timeupdate', 'ended'].forEach(event => audio.addEventListener(event, update));
        audio.addEventListener('error', () => { audio.hidden = false; controls.hidden = true; });
        seek.addEventListener('input', () => {
            if (Number.isFinite(audio.duration) && audio.duration > 0) audio.currentTime = Number(seek.value) / 100 * audio.duration;
        });
        if (audio.readyState >= 1) update();
    });
}
