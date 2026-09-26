import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Swiper from 'swiper';
import { A11y, Autoplay, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

export function initCharacterSpotlight(page) {
    const section = page.querySelector('[data-character-spotlight]');
    const viewport = section?.querySelector('[data-character-carousel]');
    if (!viewport) return;

    gsap.registerPlugin(ScrollTrigger);
    const wrapper = viewport.querySelector('.swiper-wrapper');
    const slides = [...wrapper.children];
    const controls = section.querySelector('[data-character-controls]');
    const playback = section.querySelector('[data-character-playback]');
    const pagination = section.querySelector('[data-character-pagination]');
    const center = Math.floor((slides.length - 1) / 2);
    let userPaused = false;

    const media = gsap.matchMedia();
    media.add({
        mobile: '(max-width: 700px)',
        tablet: '(min-width: 701px) and (max-width: 1000px)',
        desktop: '(min-width: 1001px)',
        reduced: '(prefers-reduced-motion: reduce)',
    }, (context) => {
        const { mobile, tablet, reduced } = context.conditions;
        let swiper;
        let timeline;
        let observer;
        let inView = false;
        let focused = false;
        let hovered = false;
        let completed = reduced || slides.length < 2;
        const progress = { value: 0 };
        const setTransforms = slides.map((slide) => gsap.quickSetter(slide, 'css'));
        let cardWidth;
        let gap;
        const copies = [];
        const removeCopies = () => copies.splice(0).forEach((copy) => copy.remove());
        const dots = slides.map((slide, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'swiper-pagination-bullet';
            dot.setAttribute('aria-label', `Show character ${index + 1}`);
            dot.addEventListener('click', () => {
                if (!swiper) return;
                if (swiper.params.loop) swiper.slideToLoop(index);
                else swiper.slideTo(index);
            });
            pagination.append(dot);
            return dot;
        });
        const syncPagination = (instance) => dots.forEach((dot, index) => {
            const active = index === instance.realIndex % slides.length;
            dot.classList.toggle('swiper-pagination-bullet-active', active);
            dot.setAttribute('aria-current', String(active));
        });

        section.classList.add('is-enhanced');
        controls.hidden = slides.length < 2;

        const syncAutoplay = () => {
            if (!swiper || reduced) return;
            const play = inView && !document.hidden && !userPaused && !focused && !hovered;
            if (play && !swiper.autoplay.running) swiper.autoplay.start();
            else if (!play && swiper.autoplay.running) swiper.autoplay.stop();
        };

        const startCarousel = () => {
            if (swiper) return;
            completed = true;
            section.classList.remove('is-revealing');
            slides.forEach((slide) => {
                slide.inert = false;
                slide.classList.remove('is-featured');
            });
            gsap.set(slides, { clearProps: 'transform,zIndex' });
            gsap.set(wrapper, { clearProps: 'transform' });
            // Repeat complete sets when all database cards fit on screen so
            // Swiper always has spare slides to loop beyond either edge.
            const spacing = parseFloat(getComputedStyle(section).getPropertyValue('--spot-gap'));
            const visible = Math.ceil(viewport.clientWidth / (slides[0].offsetWidth + spacing));
            const loop = slides.length > 1;
            const sets = loop ? Math.ceil((visible + 3) / slides.length) : 1;
            for (let set = 1; set < sets; set++) slides.forEach((slide) => {
                const copy = slide.cloneNode(true);
                copy.dataset.characterClone = '';
                copy.setAttribute('aria-hidden', 'true');
                copy.querySelectorAll('a').forEach((link) => { link.tabIndex = -1; });
                copies.push(copy);
                wrapper.append(copy);
            });
            swiper = new Swiper(viewport, {
                modules: [A11y, Autoplay, Navigation],
                slidesPerView: 'auto',
                centeredSlides: true,
                centeredSlidesBounds: !loop,
                centerInsufficientSlides: !loop,
                initialSlide: center,
                spaceBetween: spacing,
                loop,
                rewind: !loop,
                speed: reduced ? 0 : 900,
                grabCursor: slides.length > 1,
                watchOverflow: !loop,
                touchEventsTarget: 'container',
                touchStartPreventDefault: false,
                // Vertical wheel/touch movement stays with the page and Lenis.
                autoplay: reduced || slides.length < 2 ? false : {
                    delay: 2600, disableOnInteraction: false, waitForTransition: true,
                },
                navigation: {
                    prevEl: section.querySelector('[data-character-prev]'),
                    nextEl: section.querySelector('[data-character-next]'),
                },
                on: { init: syncPagination, realIndexChange: syncPagination },
                a11y: {
                    slideLabelMessage: 'Character {{index}} of {{slidesLength}}',
                    prevSlideMessage: 'Previous character', nextSlideMessage: 'Next character',
                },
            });
            playback.hidden = reduced || slides.length < 2;
            syncAutoplay();
        };

        const renderStack = () => {
            const p = progress.value;
            if (completed) {
                if (p >= .999 || reduced || slides.length < 2) return;
                // Return ownership to GSAP when scrolling back into the reveal.
                // Restoring the original order also removes Swiper's loop reordering.
                swiper?.destroy(true, true);
                swiper = null;
                removeCopies();
                completed = false;
                section.classList.add('is-revealing');
                slides.forEach((slide, index) => {
                    wrapper.append(slide);
                    slide.inert = index !== center;
                });
                slides[center].classList.add('is-featured');
                measure();
                return;
            }
            const peek = mobile ? 8 : tablet ? 11 : 14;
            slides.forEach((slide, index) => {
                const offset = index - center;
                const distance = Math.abs(offset);
                setTransforms[index]({
                    x: -offset * (cardWidth + gap - peek) * (1 - p),
                    y: distance * (mobile ? 7 : 10) * (1 - p),
                    rotation: offset * (mobile ? 1.5 : tablet ? 2.5 : 3.5) * (1 - p),
                    zIndex: slides.length - distance,
                });
            });
        };
        const measure = () => {
            if (completed) return;
            cardWidth = slides[0].offsetWidth;
            gap = parseFloat(getComputedStyle(section).getPropertyValue('--spot-gap'));
            gsap.set(wrapper, { x: (viewport.clientWidth - cardWidth) / 2 - center * (cardWidth + gap) });
            renderStack();
        };

        if (completed) startCarousel();
        else {
            section.classList.add('is-revealing');
            slides[center].classList.add('is-featured');
            slides.forEach((slide, index) => { slide.inert = index !== center; });
            measure();
            // A short mobile hold also preserves the stack on direct #characters links.
            // It lasts just 180px, so normal page scrolling resumes promptly.
            const sharedSlide = page.classList.contains('has-section-overlap');
            const pin = !sharedSlide && (mobile || section.offsetHeight < window.innerHeight - 110);
            timeline = gsap.timeline({
                scrollTrigger: {
                    trigger: sharedSlide ? section.previousElementSibling : section,
                    start: pin || sharedSlide ? (mobile ? 'top 78px' : 'top 96px') : 'top 62%',
                    end: pin || sharedSlide ? `+=${mobile ? 180 : tablet ? 320 : 460}` : 'top 16%',
                    pin, scrub: mobile ? .3 : .7,
                    invalidateOnRefresh: true,
                    onRefresh: measure,
                },
            }).to(progress, {
                value: 1, duration: .82, ease: 'none',
                onUpdate: renderStack, onComplete: startCarousel,
            }, .18); // Let the tight stack remain visible before spreading.
        }

        observer = new IntersectionObserver(([entry]) => {
            inView = entry.isIntersecting;
            syncAutoplay();
        }, { threshold: .2 });
        observer.observe(section);
        const togglePlayback = () => {
            userPaused = !userPaused;
            playback.textContent = userPaused ? '▶' : 'Ⅱ';
            playback.setAttribute('aria-label', userPaused ? 'Play character autoplay' : 'Pause character autoplay');
            playback.setAttribute('aria-pressed', String(userPaused));
            syncAutoplay();
        };
        const focusIn = (event) => {
            focused = event.target.matches(':focus-visible');
            // Keyboard users can reach every card without having to scroll the reveal.
            if (!completed && event.target.matches(':focus-visible')) timeline?.progress(1);
            syncAutoplay();
        };
        const focusOut = (event) => {
            focused = section.contains(event.relatedTarget) && event.relatedTarget.matches(':focus-visible');
            syncAutoplay();
        };
        const enter = (event) => { hovered = event.pointerType === 'mouse'; syncAutoplay(); };
        const leave = () => { hovered = false; syncAutoplay(); };
        const resize = () => { measure(); };
        playback.addEventListener('click', togglePlayback);
        section.addEventListener('focusin', focusIn);
        section.addEventListener('focusout', focusOut);
        viewport.addEventListener('pointerenter', enter);
        viewport.addEventListener('pointerleave', leave);
        document.addEventListener('visibilitychange', syncAutoplay);
        window.addEventListener('resize', resize);

        return () => {
            timeline?.scrollTrigger?.kill();
            timeline?.kill();
            observer?.disconnect();
            swiper?.destroy(true, true);
            removeCopies();
            dots.forEach((dot) => dot.remove());
            playback.removeEventListener('click', togglePlayback);
            section.removeEventListener('focusin', focusIn);
            section.removeEventListener('focusout', focusOut);
            viewport.removeEventListener('pointerenter', enter);
            viewport.removeEventListener('pointerleave', leave);
            document.removeEventListener('visibilitychange', syncAutoplay);
            window.removeEventListener('resize', resize);
            // Swiper may reorder looped slides; restore database order before rebuilding.
            slides.forEach((slide) => {
                wrapper.append(slide);
                slide.inert = false;
                slide.classList.remove('is-featured');
            });
            gsap.set(slides, { clearProps: 'transform,zIndex' });
            gsap.set(wrapper, { clearProps: 'transform' });
            section.classList.remove('is-enhanced', 'is-revealing');
            controls.hidden = true;
        };
    });

    return () => media.revert();
}
