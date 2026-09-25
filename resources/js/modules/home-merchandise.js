import Swiper from 'swiper';
import { Navigation, A11y } from 'swiper/modules';
import 'swiper/css';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initHomeMerchandise(page) {
    const section = page.querySelector('[data-merch-section]');
    if (!section) return;
    const reduce = matchMedia('(prefers-reduced-motion: reduce)');
    const mobile = matchMedia('(max-width: 700px)');
    const feedback = section.querySelector('[data-merch-feedback]');
    const filters = [...section.querySelectorAll('[data-merch-filter]')];
    let swiper, request, revision = 0, cardReveal;
    let results = section.querySelector('[data-merch-results]');

    const initSlider = () => {
        const element = results.querySelector('.swiper');
        section.querySelectorAll('.merch-arrow').forEach((arrow) => { arrow.hidden = !element; });
        if (!element) return;
        swiper = new Swiper(element, {
            modules: [Navigation, A11y], slidesPerView: 1.2, spaceBetween: 18,
            speed: reduce.matches ? 0 : 280, grabCursor: true, watchOverflow: true,
            breakpoints: { 701: { slidesPerView: 2.5, spaceBetween: 20 }, 1024: { slidesPerView: 4.5, spaceBetween: 20 }, 1440: { slidesPerView: 5, spaceBetween: 22 } },
            navigation: { prevEl: section.querySelector('.merch-arrow--prev'), nextEl: section.querySelector('.merch-arrow--next') },
            a11y: { containerMessage: 'Merchandise. Swipe or use the arrow buttons to browse.', prevSlideMessage: 'Previous merchandise', nextSlideMessage: 'Next merchandise' },
        });
        element.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key) || event.target.closest('button')) return;
            event.preventDefault();
            event.key === 'ArrowRight' ? swiper.slideNext() : swiper.slidePrev();
        });
    };
    const revealProducts = (filtered = false) => {
        cardReveal?.revert();
        cardReveal = gsap.context(() => {
            gsap.from(results.querySelectorAll('[data-merch-slide]'), {
                opacity: 0, y: reduce.matches ? 0 : (filtered ? 18 : mobile.matches ? 22 : 40),
                scale: reduce.matches || mobile.matches ? 1 : .97,
                duration: reduce.matches ? .18 : filtered ? .25 : .65,
                stagger: reduce.matches ? 0 : filtered ? { amount: .1 } : .09,
                delay: filtered || reduce.matches ? 0 : .25,
                ease: 'power2.out', clearProps: 'opacity,transform',
                ...(filtered ? {} : { scrollTrigger: { trigger: results, start: 'top 92%', once: true } }),
            });
        }, section);
    };
    initSlider();
    revealProducts();

    const motion = gsap.matchMedia();
    motion.add({ reduced: '(prefers-reduced-motion: reduce)', full: '(prefers-reduced-motion: no-preference)' }, (context) => {
        const reduced = context.conditions.reduced;
        gsap.timeline({ scrollTrigger: { trigger: section, start: 'top 88%', once: true } })
            .from(section.querySelector('.merch-heading'), { opacity: 0, y: reduced ? 0 : 25, duration: reduced ? .2 : .7, clearProps: 'opacity,transform' })
            .from(filters, { opacity: 0, y: reduced ? 0 : 12, duration: reduced ? .15 : .35, stagger: reduced ? 0 : .045, clearProps: 'opacity,transform' }, reduced ? 0 : .25);
    });

    const load = async (url, push = true) => {
        const current = ++revision;
        request?.abort();
        request = new AbortController();
        results.setAttribute('aria-busy', 'true');
        feedback.textContent = 'Loading merchandise…';
        try {
            const response = await fetch(url, { signal: request.signal, headers: { 'X-Home-Section': 'merchandise' } });
            if (!response.ok) throw new Error('Could not load merchandise');
            const html = await response.text();
            if (current !== revision) return;
            const next = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-merch-results]');
            if (!next) throw new Error('Missing merchandise results');
            cardReveal?.revert();
            await gsap.to(results, { opacity: 0, scale: reduce.matches ? 1 : .985, duration: reduce.matches ? .1 : .12 });
            if (current !== revision) return;
            swiper?.destroy(true, true);
            results.replaceWith(next);
            results = next;
            initSlider();
            revealProducts(true);
            filters.forEach((link) => {
                if (link.dataset.merchFilter === results.dataset.category) link.setAttribute('aria-current', 'true');
                else link.removeAttribute('aria-current');
            });
            feedback.textContent = `${results.dataset.count} items shown${results.dataset.count === '24' ? '. View All for the full collection.' : '.'}`;
            if (push) history.pushState({}, '', url);
            ScrollTrigger.refresh();
        } catch (error) {
            if (error.name !== 'AbortError' && current === revision) {
                gsap.set(results, { clearProps: 'opacity,transform' });
                feedback.textContent = 'Could not load this category. Please try again.';
            }
        } finally {
            if (current === revision) results.removeAttribute('aria-busy');
        }
    };
    filters.forEach((link) => link.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        const url = new URL(location.href);
        url.searchParams.set('merch_category', link.dataset.merchFilter);
        url.hash = 'merchandise';
        load(url);
    }));
    window.addEventListener('popstate', () => {
        const category = new URL(location.href).searchParams.get('merch_category') || 'all';
        if (category !== results.dataset.category) load(location.href, false);
    });
    reduce.addEventListener('change', () => {
        cardReveal?.revert();
        if (swiper) swiper.params.speed = reduce.matches ? 0 : 280;
    });
}

export function initJoinReveal(page) {
    const section = page.querySelector('[data-join-section]');
    if (!section) return;
    gsap.matchMedia().add({ reduced: '(prefers-reduced-motion: reduce)', full: '(prefers-reduced-motion: no-preference)' }, (context) => {
        const reduced = context.conditions.reduced;
        const short = matchMedia('(max-width: 700px)').matches;
        const duration = reduced ? .2 : short ? .4 : .65;
        const timeline = gsap.timeline({ scrollTrigger: { trigger: section, start: 'top 85%', once: true }, defaults: { opacity: 0, duration, ease: 'power2.out', clearProps: 'opacity,transform' } });
        timeline.from(section.querySelector('.join-label'), { y: reduced ? 0 : 12 })
            .from(section.querySelector('h2'), { y: reduced ? 0 : 25 }, '<.12')
            .from(section.querySelector('.join-description'), { y: reduced ? 0 : 18 }, '<.14')
            .from(section.querySelectorAll('.join-benefits li'), { y: reduced ? 0 : 15, stagger: reduced ? 0 : .09 }, '<.15')
            .from(section.querySelector('.join-button'), { scale: reduced ? 1 : .96 }, '-=.15');
    });
}
