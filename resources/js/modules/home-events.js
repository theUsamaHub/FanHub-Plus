import Swiper from 'swiper';
import { A11y, EffectCards } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-cards';
import 'swiper/css/a11y';

export function initHomeEvents(page) {
    const section = page.querySelector('[data-home-events]');
    const container = section?.querySelector('[data-event-swiper]');
    if (!container) return;

    const panels = [...section.querySelectorAll('[data-event-panel]')];
    const thumbnails = [...section.querySelectorAll('[data-event-select]')];
    const previous = section.querySelector('[data-event-prev]');
    const next = section.querySelector('[data-event-next]');
    const counter = section.querySelector('[data-event-count]');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const sync = (swiper) => {
        const active = swiper.activeIndex;
        panels.forEach((panel, index) => {
            panel.classList.toggle('is-active', index === active);
            panel.setAttribute('aria-hidden', String(index !== active));
            panel.inert = index !== active;
        });
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.classList.toggle('is-active', index === active);
            thumbnail.setAttribute('aria-pressed', String(index === active));
        });
        previous.disabled = panels.length < 2;
        next.disabled = panels.length < 2;
        counter.textContent = `${String(active + 1).padStart(2, '0')} / ${String(panels.length).padStart(2, '0')}`;
    };
    const swiper = new Swiper(container, {
        modules: [EffectCards, A11y],
        effect: 'cards',
        grabCursor: true,
        rewind: true,
        speed: motion.matches ? 0 : 550,
        cardsEffect: { rotate: false, perSlideOffset: 8, slideShadows: true },
        a11y: { containerMessage: 'Featured events. Swipe to explore.', itemRoleDescriptionMessage: 'Event' },
        on: { init: sync, slideChange: sync },
    });
    thumbnails.forEach((thumbnail, index) => thumbnail.addEventListener('click', () => swiper.slideTo(index)));
    previous.addEventListener('click', () => swiper.slidePrev());
    next.addEventListener('click', () => swiper.slideNext());
    section.querySelector('[data-event-deck]').addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        if (event.key === 'ArrowLeft') swiper.slidePrev();
        if (event.key === 'ArrowRight') swiper.slideNext();
        if (event.key === 'Home') swiper.slideTo(0);
        if (event.key === 'End') swiper.slideTo(panels.length - 1);
    });
    motion.addEventListener('change', () => { swiper.params.speed = motion.matches ? 0 : 550; });
}
