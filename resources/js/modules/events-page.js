import '../../css/pages/events.css';
import 'lenis/dist/lenis.css';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger);
const page = document.querySelector('[data-events-page], [data-event-detail]');

if (page) {
    // A single form moves into a native modal drawer on mobile, with focus trapping,
    // Escape support and an inline no-JavaScript fallback.
    const panel = page.querySelector('[data-event-filter-panel]');
    if (panel && 'HTMLDialogElement' in window) {
        const marker = document.createComment('Event filter position');
        panel.before(marker);
        const drawer = document.createElement('dialog');
        drawer.className = 'events-drawer';
        drawer.setAttribute('aria-labelledby', 'event-filter-title');
        drawer.setAttribute('data-lenis-prevent', '');
        document.body.append(drawer);
        const toggle = page.querySelector('[data-open-event-filters]');
        const mobile = matchMedia('(max-width: 700px)');
        const placeFilters = () => {
            drawer.close();
            if (mobile.matches) drawer.append(panel);
            else marker.after(panel);
            toggle.hidden = !mobile.matches;
        };
        placeFilters();
        mobile.addEventListener('change', placeFilters);
        let previousOverflow = document.body.style.overflow;
        toggle.addEventListener('click', () => {
            previousOverflow = document.body.style.overflow;
            drawer.showModal();
            document.body.style.overflow = 'hidden';
        });
        panel.querySelector('[data-close-event-filters]').addEventListener('click', () => drawer.close());
        drawer.addEventListener('click', (event) => { if (event.target === drawer && event.clientX < drawer.getBoundingClientRect().left) drawer.close(); });
        drawer.addEventListener('close', () => {
            document.body.style.overflow = previousOverflow;
            if (mobile.matches) toggle.focus({ preventScroll: true });
        });
    }

    const fallback = (image) => {
        if (image.matches?.('[data-event-image]') && image.src !== image.dataset.fallback) image.src = image.dataset.fallback;
    };
    page.addEventListener('error', (event) => fallback(event.target), true);
    page.querySelectorAll('[data-event-image]').forEach((image) => { if (image.complete && !image.naturalWidth) fallback(image); });

    const motion = gsap.matchMedia();
    motion.add('(prefers-reduced-motion: no-preference)', () => {
        const lenis = new Lenis({ autoRaf: false, smoothWheel: true, anchors: { offset: -100 },
            prevent: (node) => Boolean(node.closest('[data-lenis-prevent], [data-navigation], dialog, .event-feature__body')) });
        const tick = (time) => lenis.raf(time * 1000);
        gsap.ticker.add(tick);
        lenis.on('scroll', ScrollTrigger.update);
        const intro = page.querySelector('[data-events-intro]');
        if (intro) {
            const small = matchMedia('(max-width: 700px)').matches;
            gsap.fromTo(intro.querySelector('[data-events-title]'), {
                scale: small ? 1.12 : 1.35, y: small ? 20 : 30, opacity: .45, filter: 'blur(5px)',
            }, {
                scale: small ? .9 : .76, y: small ? -15 : -45, opacity: 1, filter: 'blur(0px)', ease: 'none',
                scrollTrigger: { trigger: intro, start: 'top 110px', end: 'bottom 65%', scrub: .5, invalidateOnRefresh: true },
            });
        }
        const hero = page.querySelector('[data-detail-hero] img');
        if (hero) gsap.from(hero, { scale: 1.035, opacity: .4, duration: .8, ease: 'power2.out', clearProps: 'transform,opacity' });
        return () => { gsap.ticker.remove(tick); lenis.destroy(); };
    });

    const featured = page.querySelector('[data-featured-events]');
    if (featured) {
        const cards = [...featured.querySelectorAll('[data-featured-event]')];
        const stage = featured.querySelector('[data-event-stage]');
        const counter = featured.querySelector('[data-story-count]');
        const scenes = gsap.matchMedia();
        scenes.add({ desktop: '(min-width: 901px) and (min-height: 650px)', reduced: '(prefers-reduced-motion: reduce)', full: '(prefers-reduced-motion: no-preference)' }, (context) => {
            const { desktop, reduced } = context.conditions;
            if (desktop && !reduced && cards.length > 1) {
                featured.classList.add('is-depth-story');
                let active = -1;
                const setActive = (index) => {
                    if (index === active) return;
                    active = index;
                    counter.textContent = String(index + 1).padStart(2, '0');
                    cards.forEach((card, i) => { card.inert = i !== index; card.style.pointerEvents = i === index ? 'auto' : 'none'; });
                };
                const tablet = innerWidth < 1200;
                const distance = tablet ? 65 : 120;
                cards.forEach((card, i) => gsap.set(card, { opacity: 0, scale: tablet ? .9 : .85, x: i % 2 ? distance : -distance, y: 50, z: tablet ? -100 : -180, transformPerspective: 1200, filter: tablet ? 'blur(3px)' : 'blur(6px)', zIndex: i + 1 }));
                const timeline = gsap.timeline({
                    scrollTrigger: { trigger: stage, start: 'top 105px', end: () => '+=' + Math.min(innerHeight * .65, 540) * cards.length,
                        pin: true, scrub: .55, anticipatePin: 1, invalidateOnRefresh: true },
                    // Keep the outgoing link active until the incoming card is visibly dominant.
                    onUpdate: () => setActive(Math.min(cards.length - 1, Math.max(0, Math.floor((timeline.time() - .45) / 1.15)))),
                });
                cards.forEach((card, i) => {
                    const start = i * 1.15;
                    timeline.to(card, { opacity: 1, duration: .4, ease: 'power1.out' }, start)
                        .to(card, { scale: 1, x: 0, y: 0, z: 0, filter: 'blur(0px)', duration: .85, ease: 'power1.inOut' }, start);
                    if (i > 0) {
                        const previous = cards[i - 1];
                        timeline.to(previous, { scale: .93, opacity: .4, x: i % 2 ? -100 : 100, y: -50, z: -120, filter: 'blur(1px)', duration: .85, ease: 'none' }, start + .08)
                            .to(previous, { opacity: 0, duration: .22 }, start + .93);
                    }
                });
                timeline.to({}, { duration: .4 });
                timeline.fromTo(featured.querySelector('[data-story-progress]'), { scaleX: 0 }, { scaleX: 1, duration: timeline.duration(), ease: 'none' }, 0);
                setActive(0);
                return () => {
                    featured.classList.remove('is-depth-story');
                    cards.forEach((card) => { card.inert = false; card.style.removeProperty('pointer-events'); });
                    counter.textContent = '01';
                };
            }
            // Tablets/mobile and reduced motion remain in ordinary document flow.
            cards.forEach((card, i) => gsap.from(card, {
                opacity: reduced ? .5 : 0, x: reduced ? 0 : (i % 2 ? 30 : -30), y: reduced ? 0 : 20,
                scale: reduced ? 1 : .94, duration: reduced ? .2 : .65, ease: 'power2.out', clearProps: 'opacity,transform',
                scrollTrigger: { trigger: card, start: 'top 90%', once: true },
            }));
        });
    }

    // Only the 12 loaded grid cards get inexpensive viewport reveals.
    gsap.matchMedia().add({ reduced: '(prefers-reduced-motion: reduce)', full: '(prefers-reduced-motion: no-preference)' }, (context) => {
        const reduced = context.conditions.reduced;
        const elements = page.querySelectorAll('[data-event-card], [data-event-reveal]');
        gsap.set(elements, { opacity: 0, y: reduced ? 0 : 25 });
        const triggers = ScrollTrigger.batch(elements, {
            start: 'top 94%', once: true, batchMax: 4,
            onEnter: (batch) => gsap.to(batch, { opacity: 1, y: 0, duration: reduced ? .2 : .55, stagger: reduced ? 0 : .065, clearProps: 'opacity,transform' }),
        });
        return () => { triggers.forEach((trigger) => trigger.kill()); gsap.killTweensOf(elements); gsap.set(elements, { clearProps: 'opacity,transform' }); };
    });
    document.fonts.ready.then(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}
