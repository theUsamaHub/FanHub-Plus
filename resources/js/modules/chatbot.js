const chat = document.querySelector('[data-chatbot]');

if (chat) {
    const panel = chat.querySelector('.fh-chat__panel');
    const launcher = chat.querySelector('.fh-chat__launcher');
    const input = chat.querySelector('textarea');
    const form = chat.querySelector('form');
    const body = chat.querySelector('.fh-chat__body');
    const messages = chat.querySelector('.fh-chat__messages');
    const suggestions = chat.querySelector('.fh-chat__suggestions');
    const typing = chat.querySelector('.fh-chat__typing');
    const error = chat.querySelector('.fh-chat__error');
    let loaded = false;
    let pending = false;
    let failed = null;

    const scroll = () => { body.scrollTop = body.scrollHeight; };

    // ── Auto-resize textarea ──
    const autoResize = () => { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 100) + 'px'; };
    input.addEventListener('input', autoResize);

    // ── Minimal markdown → HTML ──
    const renderMd = (text) => {
        let html = text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/`(.+?)`/g, '<code>$1</code>')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
            .replace(/\n/g, '<br>');
        // Basic unordered list
        html = html.replace(/(?:^|<br>)((?:\s*[-*]\s+.+<br>)+)/g, (_, block) => {
            const items = block.split('<br>').filter(l => l.trim()).map(l => '<li>' + l.replace(/^\s*[-*]\s+/, '') + '</li>').join('');
            return '<ul>' + items + '</ul>';
        });
        return html;
    };

    const addMessage = (text, role, source) => {
        const item = document.createElement('div');
        item.className = `fh-chat__message fh-chat__message--${role}`;
        if (role === 'user') {
            const paragraph = document.createElement('p');
            paragraph.textContent = text;
            item.append(paragraph);
        } else {
            const content = document.createElement('div');
            content.className = 'fh-chat__message-content';
            content.innerHTML = renderMd(text);
            const label = document.createElement('small');
            label.textContent = source === 'faq' ? 'FANHUB FAQ · VERIFIED ANSWER' : 'FANHUB AI';
            const copy = document.createElement('button');
            copy.type = 'button';
            copy.className = 'fh-chat__copy';
            copy.setAttribute('aria-label', 'Copy message');
            copy.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
            copy.addEventListener('click', () => {
                navigator.clipboard.writeText(text).then(() => { copy.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'; setTimeout(() => { copy.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>'; }, 1500); });
            });
            item.append(content, label, copy);
        }
        messages.append(item);
        scroll();
    };

    // ── Quick reply chips ──
    const quickReplies = ['Tell me more', 'Recommend something similar', 'What else can you do?'];
    const showQuickReplies = (context) => {
        const existing = messages.querySelector('.fh-chat__quick-replies');
        if (existing) existing.remove();
        const wrap = document.createElement('div');
        wrap.className = 'fh-chat__quick-replies';
        wrap.setAttribute('aria-label', 'Quick replies');
        const chips = context === 'anime' ? ['Recommend another anime', 'Is this anime suitable for kids?', 'What genre is this?'] : quickReplies;
        chips.forEach(text => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = text;
            btn.addEventListener('click', () => { wrap.remove(); send(text); });
            wrap.append(btn);
        });
        messages.append(wrap);
        scroll();
    };

    async function send(text, retry = false) {
        if (pending || !text.trim()) return;
        pending = true;
        error.hidden = true;
        typing.hidden = false;
        typing.setAttribute('aria-hidden', 'false');
        form.querySelector('button').disabled = true;
        suggestions.querySelectorAll('button').forEach(button => { button.disabled = true; });
        // Remove previous quick replies
        messages.querySelectorAll('.fh-chat__quick-replies').forEach(el => el.remove());
        if (!retry) addMessage(text, 'user');
        input.value = '';
        autoResize();
        scroll();
        try {
            const response = await fetch(chat.dataset.messageUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message: text, page: window.location.pathname }),
                signal: AbortSignal.timeout(35000),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(response.status === 429 ? 'A little too fast! Please wait a minute before trying again.' : response.status === 419 ? 'Your session expired. Refresh this page to continue.' : data.message || 'Unable to connect. Please try again.');
            addMessage(data.answer, 'assistant', data.source);
            failed = null;
            // Show quick replies after assistant response
            const isAnimeTopic = /anime|manga|recommend/i.test(text + data.answer);
            showQuickReplies(isAnimeTopic ? 'anime' : 'general');
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
