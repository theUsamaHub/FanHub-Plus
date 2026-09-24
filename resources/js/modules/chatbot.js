const chat = document.querySelector('[data-chatbot]');

if (chat) {
    const panel = chat.querySelector('.fh-chat__panel');
    const launcher = chat.querySelector('.fh-chat__launcher');
    const input = chat.querySelector('textarea');
    const form = chat.querySelector('form');
    const body = chat.querySelector('.fh-chat__body');
    const messages = chat.querySelector('.fh-chat__messages');
    const suggestions = chat.querySelector('.fh-chat__suggestions');
    const status = chat.querySelector('.fh-chat__status');
    const typing = chat.querySelector('.fh-chat__typing');
    const error = chat.querySelector('.fh-chat__error');
    let loaded = false;
    let pending = false;
    let failed = null;
    const scroll = () => { body.scrollTop = body.scrollHeight; };
    const addMessage = (text, role, source) => {
        const item = document.createElement('div');
        item.className = `fh-chat__message fh-chat__message--${role}`;
        const paragraph = document.createElement('p');
        paragraph.textContent = text;
        item.append(paragraph);
        if (role !== 'user') {
            const label = document.createElement('small');
            label.textContent = source === 'faq' ? 'FANHUB FAQ · VERIFIED ANSWER' : 'FANHUB AI';
            item.append(label);
        }
        messages.append(item);
        scroll();
    };
    async function send(text, retry = false) {
        if (pending || !text.trim()) return;
        pending = true;
        error.hidden = true;
        typing.hidden = false;
        typing.setAttribute('aria-hidden', 'false');
        status.hidden = true;
        form.querySelector('button').disabled = true;
        suggestions.querySelectorAll('button').forEach(button => { button.disabled = true; });
        if (!retry) addMessage(text, 'user');
        input.value = '';
        scroll();
        try {
            const response = await fetch(chat.dataset.messageUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message: text }),
                signal: AbortSignal.timeout(35000),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(response.status === 429 ? 'A little too fast! Please wait a minute before trying again.' : response.status === 419 ? 'Your session expired. Refresh this page to continue.' : data.message || 'Unable to connect. Please try again.');
            addMessage(data.answer, 'assistant', data.source);
            failed = null;
        } catch (exception) {
            failed = text;
            error.querySelector('span').textContent = exception.name === 'TimeoutError' ? 'The request timed out. Please try again.' : exception.message;
            error.hidden = false;
        } finally {
            pending = false;
            typing.hidden = true;
            typing.setAttribute('aria-hidden', 'true');
            form.querySelector('button').disabled = false;
            suggestions.querySelectorAll('button').forEach(button => { button.disabled = false; });
            scroll();
        }
    }
    async function loadSuggestions() {
        try {
            const response = await fetch(chat.dataset.faqUrl, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(10000) });
            if (!response.ok) throw new Error('FAQ unavailable');
            const data = await response.json();
            const questions = data.faqs.length ? data.faqs.map(faq => faq.question) : ['Recommend an anime for a beginner', 'What is the difference between anime and manga?'];
            suggestions.replaceChildren();
            questions.forEach(question => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = question;
                button.addEventListener('click', () => send(question));
                suggestions.append(button);
            });
            loaded = true;
        } catch { /* Free-form chat stays available; retry loading on next open. */ }
    }
    const setOpen = (open) => {
        panel.hidden = !open;
        launcher.setAttribute('aria-expanded', String(open));
        launcher.setAttribute('aria-label', open ? 'Close FanHub assistant' : 'Open FanHub assistant');
        if (open) { input.focus(); if (!loaded) loadSuggestions(); } else launcher.focus();
    };
    launcher.addEventListener('click', () => setOpen(panel.hidden));
    chat.querySelector('.fh-chat__close').addEventListener('click', () => setOpen(false));
    chat.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) { event.stopPropagation(); setOpen(false); } });
    form.addEventListener('submit', event => { event.preventDefault(); send(input.value.trim()); });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); form.requestSubmit(); }
    });
    error.querySelector('button').addEventListener('click', () => { if (failed) send(failed, true); });
}
