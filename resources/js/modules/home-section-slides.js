import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initHomeSectionSlides(page) {
    const sections = [...page.querySelectorAll('.home-scroll-content > section')];
    if (!sections.length) return;
    gsap.registerPlugin(ScrollTrigger);

    page.classList.add('has-section-slides');
    // These markers stay in normal flow while the panels themselves are sticky.
    // ScrollTrigger can therefore measure the character reveal at any scroll position.
    const anchors = sections.map((section, index) => {
        const anchor = document.createElement('div');
        anchor.className = 'home-slide-anchor';
        anchor.setAttribute('aria-hidden', 'true');
        section.before(anchor);
        section.classList.add('home-slide');
        section.style.setProperty('--slide-order', index + 1);
        return anchor;
    });
    // Real flow space gives every panel a reading interval, including the last one.
    // Keeping it outside the sticky element avoids its bottom-margin constraint.
    const holds = sections.map((section) => {
        const hold = document.createElement('div');
        hold.className = 'home-slide-hold';
        hold.setAttribute('aria-hidden', 'true');
        section.after(hold);
        return hold;
    });

    const media = gsap.matchMedia();
    media.add('(prefers-reduced-motion: no-preference)', () => {
        page.classList.add('has-section-overlap');
        let refreshTimer;
        let previousSizes = '';
        const navHeight = () => innerWidth <= 700 ? 78 : 96;
        const refresh = () => {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(() => ScrollTrigger.refresh(), 120);
        };
        const measure = () => {
            const mobile = innerWidth <= 700;
            const nav = navHeight();
            const sizes = `${innerWidth}:${innerHeight}:${sections.map((section) => section.offsetHeight).join(':')}`;
            if (sizes === previousSizes) return;
            previousSizes = sizes;
            const readingDistance = mobile
                ? gsap.utils.clamp(100, 170, innerHeight * .16)
                : gsap.utils.clamp(280, 520, innerHeight * .48);
            sections.forEach((section, index) => {
                // Long mobile sections finish scrolling before the next panel covers them.
                const top = Math.min(nav, innerHeight - section.offsetHeight);
                section.style.setProperty('--slide-top', `${top}px`);
                const revealDistance = section.matches('[data-character-spotlight]')
                    ? (mobile ? 180 : innerWidth <= 1000 ? 320 : 460) : 0;
                holds[index].style.height = `${readingDistance + revealDistance}px`;
            });
            refresh();
        };
        const observer = new ResizeObserver(measure);
        sections.forEach((section) => observer.observe(section));
        window.addEventListener('resize', measure);
        measure();

        sections.slice(0, -1).forEach((section, index) => {
            gsap.fromTo(section, { scale: 1, filter: 'brightness(1)' }, {
                scale: () => innerWidth <= 700 ? .985 : .96,
                filter: 'brightness(.76)',
                transformOrigin: '50% 0%',
                ease: 'none',
                scrollTrigger: {
                    trigger: anchors[index + 1],
                    start: 'top bottom', end: () => `top ${navHeight()}px`,
                    scrub: .35, invalidateOnRefresh: true,
                },
            });
        });

        const upcoming = sections.find((section) => section.matches('[data-upcoming-section]'));
        let upcomingMotion;
        const clearUpcoming = () => upcomingMotion?.revert();
        const animateUpcoming = () => {
            clearUpcoming();
            if (!upcoming) return;
            const anchor = anchors[sections.indexOf(upcoming)];
            upcomingMotion = gsap.context(() => {
                // The whole section arrives together; no slow cascade of hidden cards.
                gsap.fromTo(upcoming.querySelectorAll('.home-section-heading, .release-toolbar, .release-carousel'), {
                    y: 30, opacity: .72,
                }, {
                    y: 0, opacity: 1, ease: 'power1.out',
                    scrollTrigger: {
                        trigger: anchor, start: 'top 85%', end: () => `top ${navHeight() + 50}px`,
                        scrub: .3, invalidateOnRefresh: true,
                    },
                });
                const line = upcoming.querySelector('[data-timeline-progress]');
                if (line) gsap.fromTo(line, {
                    scaleX: innerWidth <= 700 ? 1 : .08,
                    scaleY: innerWidth <= 700 ? .08 : 1,
                }, {
                    scaleX: 1, scaleY: 1, ease: 'none',
                    scrollTrigger: {
                        trigger: anchor, start: 'top 65%', end: () => `top ${navHeight()}px`,
                        scrub: .3,
                    },
                });
            }, upcoming);
            refresh();
        };
        animateUpcoming();
        page.addEventListener('releases:before-update', clearUpcoming);
        page.addEventListener('releases:updated', animateUpcoming);
        const mobileQuery = matchMedia('(max-width: 700px)');
        mobileQuery.addEventListener('change', animateUpcoming);
        return () => {
            clearTimeout(refreshTimer);
            clearUpcoming();
            page.removeEventListener('releases:before-update', clearUpcoming);
            page.removeEventListener('releases:updated', animateUpcoming);
            mobileQuery.removeEventListener('change', animateUpcoming);
            observer.disconnect();
            window.removeEventListener('resize', measure);
            page.classList.remove('has-section-overlap');
        };
    });
    return () => {
        media.revert();
        anchors.forEach((anchor) => anchor.remove());
        holds.forEach((hold) => hold.remove());
        sections.forEach((section) => section.classList.remove('home-slide'));
        page.classList.remove('has-section-slides');
    };
}
