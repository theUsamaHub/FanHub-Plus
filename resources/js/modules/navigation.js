const header = document.querySelector('[data-site-header]');

if (header) {
    const mobileToggle = header.querySelector('[data-mobile-toggle]');
    const navigation = header.querySelector('[data-navigation]');
    const fandomGroups = [...header.querySelectorAll('[data-fandoms]')];
    const accountToggle = header.querySelector('[data-account-toggle]');
    const accountMenu = header.querySelector('#account-menu');
    const desktop = matchMedia('(min-width: 1181px)');
    const hover = matchMedia('(hover: hover) and (pointer: fine)');
    let closeTimer;

    const setFandoms = (open, activeGroup = null) => {
        clearTimeout(closeTimer);
        fandomGroups.forEach((group) => {
            const expanded = open && group === activeGroup;
            group.querySelector('[data-fandom-toggle]').setAttribute('aria-expanded', String(expanded));
            group.querySelector('[data-fandom-menu]').hidden = !expanded;
        });
    };
    const setAccount = (open) => {
        accountToggle?.setAttribute('aria-expanded', String(open));
        if (accountMenu) accountMenu.hidden = !open;
    };
    const setMobile = (open) => {
        navigation.classList.toggle('is-open', open);
        mobileToggle.setAttribute('aria-expanded', String(open));
        mobileToggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        if (!open) setFandoms(false);
    };
    const closeAll = () => { setMobile(false); setFandoms(false); setAccount(false); };

    mobileToggle.addEventListener('click', () => {
        setAccount(false);
        setMobile(mobileToggle.getAttribute('aria-expanded') !== 'true');
    });
    fandomGroups.forEach((group) => {
        const toggle = group.querySelector('[data-fandom-toggle]');
        const menu = group.querySelector('[data-fandom-menu]');
        toggle.addEventListener('click', () => { setAccount(false); setFandoms(menu.hidden, group); });
        toggle.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault(); setFandoms(true, group); menu.querySelector('a').focus();
            }
        });
        group.addEventListener('pointerenter', () => { if (desktop.matches && hover.matches) setFandoms(true, group); });
        group.addEventListener('pointerleave', () => {
            if (desktop.matches && hover.matches && !group.contains(document.activeElement)) closeTimer = setTimeout(() => setFandoms(false), 180);
        });
        group.addEventListener('focusout', (event) => { if (!group.contains(event.relatedTarget)) setFandoms(false); });
    });
    accountToggle?.addEventListener('click', () => {
        const open = accountMenu.hidden;
        setMobile(false);
        setFandoms(false);
        setAccount(open);
    });
    header.querySelector('[data-account]')?.addEventListener('focusout', (event) => {
        if (!event.currentTarget.contains(event.relatedTarget)) setAccount(false);
    });
    document.addEventListener('click', (event) => { if (!header.contains(event.target)) closeAll(); });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (accountMenu && !accountMenu.hidden) { setAccount(false); accountToggle.focus(); }
        else if (fandomGroups.some(group => !group.querySelector('[data-fandom-menu]').hidden)) {
            const active = fandomGroups.find(group => !group.querySelector('[data-fandom-menu]').hidden);
            setFandoms(false); active.querySelector('[data-fandom-toggle]').focus();
        }
        else if (navigation.classList.contains('is-open')) { setMobile(false); mobileToggle.focus(); }
    });
    desktop.addEventListener('change', closeAll);

    let scrollQueued = false;
    const updateScroll = () => { header.classList.toggle('is-scrolled', window.scrollY > 30); scrollQueued = false; };
    updateScroll();
    window.addEventListener('scroll', () => {
        if (!scrollQueued) { scrollQueued = true; requestAnimationFrame(updateScroll); }
    }, { passive: true });

    const themeToggle = header.querySelector('[data-theme-toggle]');
    const syncTheme = () => themeToggle.setAttribute('aria-checked', String(document.documentElement.dataset.theme !== 'light'));
    syncTheme();
    themeToggle.addEventListener('click', () => {
        const theme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;
        document.querySelector('meta[name="theme-color"]').content = theme === 'dark' ? '#06060e' : '#f5f3fc';
        try { localStorage.setItem('fanhub-theme', theme); } catch { /* Theme still works when storage is unavailable. */ }
        syncTheme();
    });

    const searchDialog = document.querySelector('[data-search-dialog]');
    header.querySelector('[data-search-open]').addEventListener('click', () => {
        closeAll();
        searchDialog.showModal();
        searchDialog.querySelector('input').focus();
    });
    searchDialog.querySelector('[data-search-close]').addEventListener('click', () => searchDialog.close());
    searchDialog.addEventListener('click', (event) => {
        const rect = searchDialog.getBoundingClientRect();
        if (event.target === searchDialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) searchDialog.close();
    });
}
