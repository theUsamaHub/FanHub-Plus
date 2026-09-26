import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initHomeSectionSlides(page) {
    const allSections = [...page.querySelectorAll('.home-scroll-content > section')];
    const slideSelector = '#trending, #featured-story, #characters, #home-events';
    const sections = allSections.filter((section) => section.matches(slideSelector));
    if (!sections.length) return;
    gsap.registerPlugin(ScrollTrigger);

    // Bound sticky panels to consecutive slide sections. A normal-flow section
    // ends the group, so no pinned panel can cover Multimedia, Upcoming, etc.
    const groups = [];
    let group;
    allSections.forEach((section) => {
        if (!section.matches(slideSelector)) {
            group = null;
            return;
        }
        if (!group) {
            group = document.createElement('div');
            group.className = 'home-slide-group';
            section.before(group);
            groups.push(group);
        }
        group.append(section);
    });

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
                ? gsap.utils.clamp(140, 240, innerHeight * .24)
                : gsap.utils.clamp(480, 850, innerHeight * .75);
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
            if (section.parentElement !== sections[index + 1].parentElement) return;
            gsap.fromTo(section, { scale: 1, filter: 'brightness(1)' }, {
                scale: () => innerWidth <= 700 ? .985 : .96,
                filter: 'brightness(.76)',
                transformOrigin: '50% 0%',
                ease: 'none',
                scrollTrigger: {
                    trigger: anchors[index + 1],
                    start: 'top bottom', end: () => `top ${navHeight()}px`,
                    scrub: 1.1, invalidateOnRefresh: true,
                },
            });
        });

        return () => {
            clearTimeout(refreshTimer);
            observer.disconnect();
            window.removeEventListener('resize', measure);
            page.classList.remove('has-section-overlap');
        };
    });
    return () => {
        media.revert();
        anchors.forEach((anchor) => anchor.remove());
        holds.forEach((hold) => hold.remove());
        sections.forEach((section) => {
            section.classList.remove('home-slide');
            section.style.removeProperty('--slide-order');
            section.style.removeProperty('--slide-top');
        });
        groups.forEach((group) => group.replaceWith(...group.childNodes));
        page.classList.remove('has-section-slides');
    };
}
