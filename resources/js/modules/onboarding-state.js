export function fandomOnboarding() {
    return {
        selected: [], submitting: false, message: '',
        get valid() { return this.selected.length >= 3 && this.selected.length <= 5; },
        get hint() {
            if (this.selected.length < 3) return `Pick ${3 - this.selected.length} more to continue.`;
            return this.selected.length === 5 ? 'All five chosen. Ready when you are.' : 'Great choices. Add more or make it yours.';
        },
        init() {
            this.selected = [...new Set(JSON.parse(this.$el.dataset.selected).map(String))];
            const dialog = this.$el;
            if (typeof dialog.showModal === 'function') {
                dialog.removeAttribute('open');
                dialog.showModal();
            }
            // Native dialogs provide focus containment; this also covers older browsers.
            dialog.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') { event.preventDefault(); return; }
                if (event.key !== 'Tab') return;
                const items = [...dialog.querySelectorAll('input:not([type="hidden"]):not(:disabled), button:not(:disabled)')];
                const first = items[0], last = items.at(-1);
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
            });
            this.$watch('selected', () => { this.message = this.selected.length === 5 ? 'Five favorites selected. Deselect one to choose another.' : ''; });
            window.addEventListener('pageshow', () => { this.submitting = false; });
        },
        submit(event) {
            if (!this.valid || this.submitting) { event.preventDefault(); this.message = 'Please choose between 3 and 5 fandoms.'; return; }
            // Keep successful checkbox values enabled until the browser serializes the form.
            this.submitting = true;
            this.message = 'Saving your favorites...';
        },
    };
}
