import '../../css/pages/fan-content.css';

const dialog = document.querySelector('[data-fan-dialog]');
if (dialog && typeof dialog.showModal === 'function') {
    const detail = dialog.querySelector('[data-fan-detail]');
    const status = dialog.querySelector('[data-fan-status]');
    const error = dialog.querySelector('[data-fan-error]');
    const fallback = dialog.querySelector('[data-fan-fallback]');
    let controller;
    let activeUrl;
    let trigger;
    let previousOverflow;
    const load = async () => {
        controller?.abort();
        controller = new AbortController();
        const request = controller;
        const timeout = setTimeout(() => request.abort(), 20000);
        detail.replaceChildren();
        detail.setAttribute('aria-busy', 'true');
        error.hidden = true;
        status.hidden = false;
        try {
            const response = await fetch(activeUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' }, signal: request.signal });
            if (!response.ok) throw new Error('Creation unavailable');
            const html = await response.text();
            if (controller !== request || !dialog.open) return;
            // Only our server-rendered, sanitized detail endpoint is inserted here.
            detail.innerHTML = html;
            if (!detail.querySelector('.fan-detail')) throw new Error('Unexpected response');
        } catch {
            if (controller !== request || !dialog.open) return;
            detail.replaceChildren();
            error.hidden = false;
        } finally {
            clearTimeout(timeout);
            if (controller === request) {
                status.hidden = true;
                detail.removeAttribute('aria-busy');
            }
        }
    };
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-fan-open]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        trigger = link;
        activeUrl = link.href;
        fallback.href = activeUrl;
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        dialog.showModal();
        dialog.scrollTop = 0;
        dialog.querySelector('[data-fan-close]').focus();
        load();
    });
    dialog.querySelector('[data-fan-close]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-fan-retry]').addEventListener('click', load);
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
        controller?.abort();
        controller = null;
        detail.querySelectorAll('video, audio').forEach(media => media.pause());
        detail.replaceChildren();
        document.body.style.overflow = previousOverflow ?? '';
        trigger?.focus({ preventScroll: true });
    });
}
