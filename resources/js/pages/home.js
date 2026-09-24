import '../../css/pages/home.css';
import 'lenis/dist/lenis.css';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import { initReleaseTimeline } from '../modules/release-timeline';
import { initCharacterSpotlight } from '../modules/character-spotlight';
import { initHomeSectionSlides } from '../modules/home-section-slides';
import { revealCards, revealHeading } from '../modules/home-reveals';
import '../modules/hero-video';

const page = document.querySelector('[data-page="home"]');

if (page) {
    gsap.registerPlugin(ScrollTrigger);
    initReleaseTimeline(page);
    initHomeSectionSlides(page);
    initCharacterSpotlight(page);

    const motion = gsap.matchMedia();
    motion.add('(prefers-reduced-motion: no-preference)', () => {
        // GSAP owns the smooth-scroll clock; Swiper is scoped to the character section.
        const lenis = new Lenis({
            autoRaf: false,
            smoothWheel: true,
            lerp: .075,
            wheelMultiplier: .85,
            anchors: { offset: -90 },
            prevent: (node) => Boolean(node.closest('[data-lenis-prevent], [data-navigation], dialog')),
        });
        const tick = (time) => lenis.raf(time * 1000);
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add(tick);
        gsap.ticker.lagSmoothing(0);

        if (!page.classList.contains('has-section-slides')) {
            page.querySelectorAll('[data-reveal]').forEach(revealHeading);
            page.querySelectorAll('[data-stagger]').forEach((group) => {
                if (group.closest('[data-upcoming-section]')) return;
                revealCards(group.querySelectorAll('[data-stagger-item]'));
            });
        }

        let timelineContext;
        const revealTimeline = () => {
            timelineContext?.revert();
            if (page.classList.contains('has-section-slides')) {
                ScrollTrigger.refresh();
                return;
            }
            timelineContext = gsap.context(() => {
                const timeline = page.querySelector('[data-release-results]');
                if (!timeline) return;
                const cards = timeline.querySelectorAll('[data-release-card]');
                const isMobile = matchMedia('(max-width: 700px)').matches;
                if (isMobile) revealCards(cards);
                else if (cards.length) gsap.from(cards, {
                    autoAlpha: 0, y: 36, stagger: .12, duration: .8, ease: 'power2.out',
                    scrollTrigger: { trigger: timeline, start: 'top 92%', once: true },
                });
                cards.forEach((card) => {
                    gsap.from(card.querySelector('.release-node'), {
                        scale: .45, duration: .5, ease: 'power2.out',
                        scrollTrigger: { trigger: card, start: 'top 80%', once: true },
                    });
                });
                const progress = timeline.querySelector('[data-timeline-progress]');
                if (progress) {
                    gsap.from(progress, {
                        [isMobile ? 'scaleY' : 'scaleX']: 0,
                        ease: 'none',
                        scrollTrigger: { trigger: timeline, start: 'top 85%', end: 'bottom 75%', scrub: .5 },
                    });
                }
            }, page);
            ScrollTrigger.refresh();
        };
        revealTimeline();
        const clearTimeline = () => timelineContext?.revert();
        page.addEventListener('releases:before-update', clearTimeline);
        page.addEventListener('releases:updated', revealTimeline);
        const mobile = matchMedia('(max-width: 700px)');
        mobile.addEventListener('change', revealTimeline);

        return () => {
            page.removeEventListener('releases:updated', revealTimeline);
            page.removeEventListener('releases:before-update', clearTimeline);
            mobile.removeEventListener('change', revealTimeline);
            timelineContext?.revert();
            gsap.ticker.remove(tick);
            lenis.destroy();
        };
    });

    const parallax = gsap.matchMedia();
    parallax.add('(min-width: 901px) and (prefers-reduced-motion: no-preference)', () => {
        page.querySelectorAll('[data-parallax]').forEach((image) => {
            gsap.fromTo(image, { yPercent: -2 }, {
                yPercent: 3, ease: 'none',
                scrollTrigger: { trigger: image.closest('.story-card'), start: 'top bottom', end: 'bottom top', scrub: true },
            });
        });
    });

    document.fonts.ready.then(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}
