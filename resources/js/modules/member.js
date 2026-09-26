document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelector('[data-find-nearby]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const status = document.querySelector('[data-location-status]');
    const form = document.querySelector('[data-nearby-form]');
    if (!navigator.geolocation) { status.textContent = 'Location is unavailable. Enter coordinates or browse by city instead.'; return; }
    button.disabled = true;
    status.textContent = 'Finding your location…';
    navigator.geolocation.getCurrentPosition(position => {
        form.elements.latitude.value = position.coords.latitude.toFixed(4);
        form.elements.longitude.value = position.coords.longitude.toFixed(4);
        button.disabled = false;
        status.textContent = 'Location found. Searching nearby events…';
        form.requestSubmit();
    }, error => {
        button.disabled = false;
        status.textContent = error.code === 1 ? 'Location access was declined. Enter coordinates or browse by city instead.' : 'Could not find your location. Please try again or enter coordinates.';
    }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
});

document.querySelectorAll('[data-share-url]').forEach(button => {
    button.addEventListener('click', async () => {
        const status = button.parentElement.querySelector('[data-share-status]');
        try {
            if (navigator.share) await navigator.share({ title: button.dataset.shareTitle, url: button.dataset.shareUrl });
            else {
                await navigator.clipboard.writeText(button.dataset.shareUrl);
                status.textContent = 'Link copied.';
            }
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'Copy the address from your browser to share this page.';
        }
    });
});

const preferenceUrl = document.querySelector('meta[name="member-preferences-url"]')?.content;
if (preferenceUrl) {
    document.querySelector('[data-theme-toggle]')?.addEventListener('click', async () => {
        try {
            const response = await fetch(preferenceUrl, {
                method: 'PATCH', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ theme_preference: document.documentElement.dataset.theme }),
            });
            if (!response.ok) throw new Error('Preference was not saved');
        } catch { /* The local theme toggle remains available offline. */ }
    });
}
