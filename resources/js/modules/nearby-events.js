export function initNearbyEvents(page, refresh = () => {}) {
    const controls = page.querySelector('[data-nearby-controls]');
    if (!controls) return;
    const start = controls.querySelector('[data-nearby-start]');
    const reset = controls.querySelector('[data-nearby-reset]');
    const radius = controls.querySelector('[data-nearby-radius]');
    const radiusLabel = controls.querySelector('[data-nearby-radius-label]');
    const status = controls.querySelector('[data-nearby-status]');
    const locationInfo = controls.querySelector('[data-nearby-location]');
    const accuracyInfo = controls.querySelector('[data-nearby-accuracy]');
    const locationMap = controls.querySelector('[data-nearby-map]');
    const clearFilters = controls.querySelector('[data-nearby-clear-filters]');
    const results = page.querySelector('[data-event-results]');
    const form = page.querySelector('.events-filter-form');
    const featured = page.querySelector('[data-featured-events]');
    let coordinates = null;
    let originalResults = null;
    let sequence = 0;
    let pending = null;
    let accuracy = null;
    controls.hidden = false;

    const busy = (value) => {
        start.disabled = value;
        radius.disabled = value;
        results.setAttribute('aria-busy', String(value));
        controls.setAttribute('aria-busy', String(value));
    };
    const search = async (pageNumber = 1) => {
        const current = ++sequence;
        pending?.abort();
        pending = new AbortController();
        const controller = pending;
        const timeout = setTimeout(() => controller.abort(), 15000);
        busy(true);
        status.textContent = `Loading events within ${radius.value} km...`;
        try {
            const data = Object.fromEntries(new FormData(form));
            const hasFilters = ['q', 'category', 'city', 'type', 'date', 'when'].some((key) => String(data[key] ?? '').trim() !== '');
            if (clearFilters) clearFilters.hidden = !hasFilters;
            const response = await fetch(controls.dataset.endpoint, {
                method: 'POST', credentials: 'same-origin', signal: pending.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ ...data, ...coordinates, radius: Number(radius.value), page: pageNumber }),
            });
            if (!response.ok) throw new Error('request');
            const payload = await response.json();
            if (current !== sequence) return;
            if (originalResults === null) originalResults = results.innerHTML;
            results.innerHTML = payload.html;
            if (!globalThis.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
                results.querySelectorAll('[data-event-card]').forEach((card, index) => {
                    card.animate?.([{ opacity: 0, transform: 'translateY(18px)' }, { opacity: 1, transform: 'translateY(0)' }],
                        { duration: 420, delay: Math.min(index, 5) * 55, easing: 'ease-out', fill: 'backwards' });
                });
            }
            if (featured) featured.hidden = true;
            const approximate = accuracy !== null && accuracy > Number(radius.value) * 1000;
            status.textContent = payload.total
                ? `${payload.total} events within ${radius.value} km of your detected location. Distances are straight-line estimates.`
                : approximate
                    ? `Your browser location is only accurate to about ${(accuracy / 1000).toFixed(1)} km, which is wider than this ${radius.value} km search. Check the detected pin or refresh your location before concluding there are no nearby events.`
                    : `No events found within ${radius.value} km of your detected location${hasFilters ? ' with these filters. Clear filters and search nearby, or choose a larger radius.' : '. Check the detected pin or choose a larger search radius.'}`;
            if (!payload.total && Number.isFinite(payload.nearest_distance_km)) {
                status.textContent += ` The nearest matching event is ${payload.nearest_distance_km.toFixed(1)} km from that pin.`;
            }
            refresh();
        } catch {
            if (current === sequence) status.textContent = 'Nearby events could not be loaded. Try again or choose Show all events. Your previous results are still available.';
        } finally {
            clearTimeout(timeout);
            if (current === sequence) busy(false);
        }
    };
    start.addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Your browser does not support location access. You can still browse all events.';
            return;
        }
        const current = ++sequence;
        busy(true);
        reset.hidden = false;
        status.textContent = 'Requesting your location... Please allow location access in your browser.';
        navigator.geolocation.getCurrentPosition((position) => {
            if (current !== sequence) return;
            const { latitude, longitude, accuracy: reportedAccuracy } = position.coords;
            if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || Math.abs(latitude) > 90 || Math.abs(longitude) > 180) {
                busy(false);
                status.textContent = 'Your browser did not provide a valid location. Please turn on device location and try again.';
                return;
            }
            coordinates = { latitude, longitude };
            accuracy = Number.isFinite(reportedAccuracy) && reportedAccuracy > 0 ? reportedAccuracy : null;
            if (locationInfo) locationInfo.hidden = false;
            if (accuracyInfo) accuracyInfo.textContent = accuracy === null
                ? 'Browser location received; accuracy is unavailable.'
                : `Browser location accuracy: about ${accuracy < 1000 ? `${Math.ceil(accuracy)} m` : `${(accuracy / 1000).toFixed(1)} km`}.`;
            if (locationMap) locationMap.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${latitude},${longitude}`)}`;
            radius.value = '5';
            radiusLabel.hidden = false;
            search();
        }, (error) => {
            if (current !== sequence) return;
            busy(false);
            status.textContent = error.code === 1
                ? 'Location permission was denied. Allow location access to try again, or continue browsing all events.'
                : error.code === 3 ? 'Location request timed out. Please try again or browse all events.'
                    : 'Your location could not be determined. Please try again or browse all events.';
        }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
    });
    clearFilters?.addEventListener('click', () => {
        if (!coordinates) return;
        for (const name of ['q', 'category', 'city', 'type', 'date', 'when']) {
            const input = form.elements.namedItem(name);
            if (input) input.value = '';
        }
        search();
    });
    radius.addEventListener('change', () => { if (coordinates) search(); });
    form.addEventListener('submit', (event) => {
        if (!coordinates) return;
        event.preventDefault();
        form.closest('dialog')?.close();
        search();
    });
    results.addEventListener('click', (event) => {
        const link = event.target.closest('.events-pagination a');
        if (!coordinates || !link) return;
        event.preventDefault();
        search(Number(new URL(link.href).searchParams.get('page') || 1));
    });
    reset.addEventListener('click', () => {
        ++sequence;
        pending?.abort();
        coordinates = null;
        accuracy = null;
        if (locationInfo) locationInfo.hidden = true;
        if (locationMap) locationMap.removeAttribute('href');
        if (accuracyInfo) accuracyInfo.textContent = '';
        if (clearFilters) clearFilters.hidden = true;
        if (originalResults !== null) results.innerHTML = originalResults;
        originalResults = null;
        results.querySelectorAll('[data-event-card]').forEach((card) => {
            card.style.removeProperty('opacity');
            card.style.removeProperty('transform');
        });
        if (featured) featured.hidden = false;
        radiusLabel.hidden = true;
        reset.hidden = true;
        form.reset();
        busy(false);
        status.textContent = 'Showing the original event listing. Your location has been cleared.';
        refresh();
    });
}
