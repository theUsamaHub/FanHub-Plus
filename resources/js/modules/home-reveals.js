import { gsap } from 'gsap';

// Each card owns its viewport trigger: stacked mobile cards must not finish
// animating while the visitor is still reading the first card in the group.
export function revealCards(cards) {
    [...cards].forEach((card, index) => {
        gsap.from(card, {
            autoAlpha: 0,
            y: 40,
            duration: .8,
            delay: matchMedia('(min-width: 1101px)').matches ? (index % 3) * .08 : 0,
            ease: 'power2.out',
            scrollTrigger: { trigger: card, start: 'top 88%', once: true },
        });
    });
}

export function revealHeading(element) {
    const parts = element.matches('.home-section-heading') ? element.children : [element];
    gsap.from(parts, {
        autoAlpha: 0,
        y: element.dataset.reveal === 'fade' ? 0 : 28,
        duration: .7,
        stagger: .1,
        ease: 'power2.out',
        scrollTrigger: { trigger: element, start: 'top 85%', once: true },
    });
}
