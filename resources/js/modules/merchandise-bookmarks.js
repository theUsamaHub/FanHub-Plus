const status = document.querySelector('[data-bookmark-status]');
const dialog = document.querySelector('[data-merch-login]');
let toastTimer;
const announce = (message) => {
    if (!status) return;
    clearTimeout(toastTimer);
    status.textContent = message;
    status.hidden = false;
    toastTimer = setTimeout(() => { status.hidden = true; }, 4000);
};

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-bookmark-login]') && dialog?.showModal) {
        event.preventDefault();
        dialog.showModal();
    }
    if (event.target.closest('[data-close-merch-login]')) dialog?.close();
});
dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-merch-bookmark]');
    if (!form) return;
    event.preventDefault();
    const button = form.querySelector('button');
    if (button.disabled) return;
    button.disabled = true;
    try {
        const response = await fetch(form.action, {
            method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' },
        });
        if (response.status === 401 || response.status === 419) {
            announce('Your session expired. Please log in again to save this item.');
            return;
        }
        if (!response.ok) throw new Error('Save failed');
        const result = await response.json();
        document.querySelectorAll('[data-merch-bookmark]').forEach((other) => {
            if (other.dataset.item !== form.dataset.item) return;
            other.elements.saved.value = result.saved ? '0' : '1';
            const heart = other.querySelector('button');
            heart.setAttribute('aria-pressed', String(result.saved));
            heart.setAttribute('aria-label', result.saved ? `Remove ${heart.dataset.name} from bookmarks` : `Bookmark ${heart.dataset.name}`);
        });
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
            button.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.15)' }, { transform: 'scale(1)' }], { duration: 280 });
        }
        announce(result.message);
    } catch {
        announce('Could not update your bookmark. Please try again.');
    } finally {
        button.disabled = false;
    }
});

const fallback = (image) => {
    if (!image.matches?.('[data-merch-image]') || image.src === image.dataset.fallback) return;
    image.src = image.dataset.fallback;
};
document.addEventListener('error', (event) => fallback(event.target), true);
document.querySelectorAll('[data-merch-image]').forEach((image) => {
    if (image.complete && !image.naturalWidth) fallback(image);
});

const products = document.getElementById('merch-products');
const viewBtns = document.querySelectorAll('.merch-view-btn');
if (products && viewBtns.length) {
    viewBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const view = btn.dataset.view;
            viewBtns.forEach((b) => b.setAttribute('aria-pressed', 'false'));
            btn.setAttribute('aria-pressed', 'true');
            products.dataset.view = view;
            document.cookie = `merch_view=${view}; path=/; max-age=${60 * 60 * 24 * 30}; SameSite=Lax`;
        });
    });
}
