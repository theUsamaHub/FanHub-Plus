import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initExploreFandoms(page) {
    const section = page.querySelector('[data-explore-fandoms]');
    if (!section) return;

    const cluster = section.querySelector('[data-fandom-cluster]');
    if (!cluster) return;

    const items = [...cluster.querySelectorAll('[data-fandom-item]')];
    if (!items.length) return;

    gsap.registerPlugin(ScrollTrigger);

    const centerIndex = Math.floor((items.length - 1) / 2);
    items[centerIndex]?.classList.add('is-primary');

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Handle Reduced Motion
    if (prefersReducedMotion) {
        section.classList.add('is-static');
        items.forEach((item) => {
            gsap.set(item, {
                position: 'relative',
                left: 'auto',
                top: 'auto',
                x: 0,
                y: 0,
                scale: 1,
                opacity: 1,
            });
        });
        return;
    }

    let isExpanded = false;
    let selectedItem = null;

    // Compute relative layout coordinates for stacked vs expanded state
    const computePositions = (expanded = false) => {
        const isMobile = window.innerWidth <= 700;
        const isTablet = window.innerWidth > 700 && window.innerWidth <= 1024;
        
        const itemWidth = items[0].offsetWidth || 150;
        const total = items.length;
        const middle = (total - 1) / 2;

        // In stacked mode, circles overlap significantly. Center circle is on top.
        const stackedStep = isMobile ? itemWidth * 0.35 : isTablet ? itemWidth * 0.28 : itemWidth * 0.26;
        
        // In expanded/hover mode, circles spread out horizontally with crisp gaps
        const expandedStep = isMobile ? itemWidth * 0.88 : isTablet ? itemWidth * 1.02 : itemWidth * 1.08;

        const currentStep = expanded ? expandedStep : stackedStep;

        return items.map((item, index) => {
            const distance = Math.abs(index - centerIndex);
            const x = (index - middle) * currentStep;
            
            let scale = 1;
            if (!expanded) {
                scale = index === centerIndex ? 1.15 : Math.max(0.82, 0.98 - distance * 0.045);
            } else {
                scale = index === centerIndex ? 1.06 : 1.0;
            }

            // zIndex decreases as distance from center increases
            const zIndex = total - distance;

            return { x, y: 0, scale, zIndex };
        });
    };

    // Apply computed positions via GSAP
    const applyLayout = (expanded = false, animate = true) => {
        isExpanded = expanded;
        section.classList.toggle('is-expanded', expanded);
        const positions = computePositions(expanded);

        items.forEach((item, index) => {
            const pos = positions[index];
            const isHovered = selectedItem === item;
            const targetScale = (isHovered && expanded) ? pos.scale * 1.08 : pos.scale;

            const vars = {
                x: pos.x,
                y: pos.y,
                scale: targetScale,
                zIndex: isHovered ? 50 : pos.zIndex,
                duration: animate ? 0.55 : 0,
                ease: 'power3.out',
                overwrite: 'auto',
            };

            gsap.to(item, vars);
        });
    };

    // Viewport Entrance Timeline with ScrollTrigger
    const entranceTl = gsap.timeline({
        scrollTrigger: {
            trigger: section,
            start: 'top 85%',
            once: true,
        },
    });

    // 1. Heading fades up
    const heading = section.querySelector('.explore-fandoms__heading');
    if (heading) {
        entranceTl.from(heading, {
            opacity: 0,
            y: 24,
            duration: 0.6,
            ease: 'power2.out',
        });
    }

    // Initial stacked layout without animation
    applyLayout(false, false);

    // 2. Stagger reveal: Center circle appears first, remaining circles reveal from behind
    const revealOrder = [...items].sort((a, b) => {
        const distA = Math.abs(items.indexOf(a) - centerIndex);
        const distB = Math.abs(items.indexOf(b) - centerIndex);
        return distA - distB;
    });

    entranceTl.from(
        revealOrder,
        {
            opacity: 0,
            scale: 0.6,
            duration: 0.5,
            stagger: 0.07,
            ease: 'back.out(1.4)',
            clearProps: 'opacity',
        },
        '-=0.3'
    );

    // Desktop Hover Interactions
    const onMouseEnter = () => {
        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            applyLayout(true, true);
        }
    };

    const onMouseLeave = () => {
        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            selectedItem = null;
            items.forEach((el) => el.classList.remove('is-selected'));
            applyLayout(false, true);
        }
    };

    cluster.addEventListener('pointerenter', onMouseEnter);
    cluster.addEventListener('pointerleave', onMouseLeave);

    // Individual Circle Hover handling inside cluster
    items.forEach((item) => {
        item.addEventListener('pointerenter', () => {
            if (isExpanded && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                selectedItem = item;
                const pos = computePositions(true)[items.indexOf(item)];
                gsap.to(item, { scale: pos.scale * 1.08, zIndex: 50, duration: 0.3, ease: 'power2.out', overwrite: 'auto' });
            }
        });

        item.addEventListener('pointerleave', () => {
            if (isExpanded && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                if (selectedItem === item) selectedItem = null;
                const pos = computePositions(true)[items.indexOf(item)];
                gsap.to(item, { scale: pos.scale, zIndex: pos.zIndex, duration: 0.3, ease: 'power2.out', overwrite: 'auto' });
            }
        });

        // Mobile / Touch Tap Handling
        item.addEventListener('click', (e) => {
            const isTouch = !window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            if (isTouch) {
                if (!isExpanded) {
                    e.preventDefault();
                    applyLayout(true, true);
                    item.classList.add('is-selected');
                    selectedItem = item;
                } else if (selectedItem !== item) {
                    e.preventDefault();
                    items.forEach((el) => el.classList.remove('is-selected'));
                    item.classList.add('is-selected');
                    selectedItem = item;
                    applyLayout(true, true);
                }
                // Second tap on the same selected circle proceeds with category navigation link
            }
        });
    });

    // Window Resize Handler
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            applyLayout(isExpanded, false);
        }, 100);
    });
}
