<div
    x-data="commandPalette()"
    x-on:keydown.window.prevent.ctrl.k="toggle()"
    x-on:keydown.window.prevent.meta.k="toggle()"
    x-on:keydown.escape.window="close()"
    x-on:toggle-command-palette.window="open = true; $nextTick(() => { query = ''; activeIndex = 0; $refs.searchInput?.focus(); })"
    x-cloak
>
    <div x-show="open" x-transition.opacity.200ms class="fh-adm-palette-overlay" x-on:click="close()"></div>

    <div
        x-show="open"
        x-transition:enter="fh-adm-palette-enter"
        x-transition:enter-start="fh-adm-palette-enter-start"
        x-transition:enter-end="fh-adm-palette-enter-end"
        x-transition:leave="fh-adm-palette-leave"
        x-transition:leave-start="fh-adm-palette-leave-start"
        x-transition:leave-end="fh-adm-palette-leave-end"
        class="fh-adm-palette"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('Command palette') }}"
    >
        <div class="card border-0">
            <div class="card-body p-0">
                <div class="px-3 py-3" style="border-bottom:1px solid var(--fh-adm-line-soft);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-search text-secondary flex-shrink-0"></i>
                        <input
                            type="text"
                            class="form-control border-0 shadow-none px-0"
                            x-ref="searchInput"
                            x-model="query"
                            x-on:keydown="handleKeydown($event)"
                            placeholder="Search pages..."
                            style="outline:none;font-size:0.95rem;caret-color:var(--fh-cyan);background:transparent;"
                        >
                        <kbd class="fh-adm-kbd">ESC</kbd>
                    </div>
                </div>
                <ul class="list-unstyled mb-0" style="max-height:380px;overflow-y:auto;">
                    <template x-for="(item, index) in filtered" :key="item.url">
                        <li>
                            <a
                                :href="item.url"
                                class="fh-adm-palette-item"
                                :class="index === activeIndex ? 'is-active' : ''"
                                @click.prevent="go(item)"
                                @mouseenter="activeIndex = index"
                            >
                                <i :class="item.icon" class="text-center flex-shrink-0" style="width:1.2rem;font-size:0.9rem;"></i>
                                <span class="flex-grow-1" x-text="item.name" style="font-size:0.85rem;"></span>
                                <span class="badge fw-normal" :class="index === activeIndex ? 'text-bg-primary' : 'text-bg-secondary'" x-text="item.category" style="font-size:0.6rem;"></span>
                            </a>
                        </li>
                    </template>
                    <li x-show="filtered.length === 0 && query.length > 0">
                        <div class="px-3 py-5 text-center">
                            <i class="bi bi-search fs-3 d-block mx-auto mb-2" style="color:var(--fh-adm-dim);"></i>
                            <small style="color:var(--fh-adm-muted);">No results for "<span x-text="query" style="color:var(--fh-adm-text);font-weight:500;"></span>"</small>
                        </div>
                    </li>
                </ul>
                <div class="px-3 py-2 d-flex gap-3 justify-content-end" style="font-size:0.6rem;border-top:1px solid var(--fh-adm-line-soft);background:rgba(0,0,0,0.2);">
                    <span style="color:var(--fh-adm-dim);"><kbd class="fh-adm-kbd">↑</kbd> <kbd class="fh-adm-kbd">↓</kbd> Navigate</span>
                    <span style="color:var(--fh-adm-dim);"><kbd class="fh-adm-kbd">↵</kbd> Open</span>
                    <span style="color:var(--fh-adm-dim);"><kbd class="fh-adm-kbd">ESC</kbd> Close</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fh-adm-palette-enter { transition: all 0.35s cubic-bezier(0.34, 1.4, 0.64, 1); }
    .fh-adm-palette-enter-start { opacity: 0; transform: translateX(-50%) translateY(-12px) scale(0.96); }
    .fh-adm-palette-enter-end { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    .fh-adm-palette-leave { transition: all 0.2s ease-in; }
    .fh-adm-palette-leave-start { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    .fh-adm-palette-leave-end { opacity: 0; transform: translateX(-50%) translateY(-8px) scale(0.97); }
</style>

<script>
function commandPalette() {
    return {
        open: false,
        query: '',
        activeIndex: 0,
        pages: [
            { name: 'Profile', url: '{{ route("profile.edit") }}', icon: 'bi bi-person', category: 'General' },
            @if(auth()->user()->hasRole('admin'))
            { name: 'Admin Dashboard', url: '{{ route("admin.dashboard") }}', icon: 'bi bi-speedometer2', category: 'Admin' },
            { name: 'Content', url: '{{ route("admin.contents.index") }}', icon: 'bi bi-file-earmark-text', category: 'Content' },
            { name: 'Submissions', url: '{{ route("admin.submissions.index") }}', icon: 'bi bi-inbox', category: 'Content' },
            { name: 'Characters', url: '{{ route("admin.characters.index") }}', icon: 'bi bi-person-badge', category: 'Content' },
            { name: 'Merchandise', url: '{{ route("admin.merchandise.index") }}', icon: 'bi bi-box-seam', category: 'Discovery' },
            { name: 'Events', url: '{{ route("admin.events.index") }}', icon: 'bi bi-calendar-event', category: 'Discovery' },
            { name: 'Reviews', url: '{{ route("admin.reviews.index") }}', icon: 'bi bi-chat-left-text', category: 'Community' },
            { name: 'Ratings', url: '{{ route("admin.ratings.index") }}', icon: 'bi bi-star', category: 'Community' },
            { name: 'Feedback', url: '{{ route("admin.feedback.index") }}', icon: 'bi bi-megaphone', category: 'Community' },
            { name: 'Chatbot FAQs', url: '{{ route("admin.chatbot.faqs.index") }}', icon: 'bi bi-question-circle', category: 'Content' },
            { name: 'Categories', url: '{{ route("admin.categories.index") }}', icon: 'bi bi-tags', category: 'Content' },
            { name: 'Recycle Bin', url: '{{ route("admin.categories.trashed") }}', icon: 'bi bi-trash', category: 'Content' },
            { name: 'Tags', url: '{{ route("admin.tags.index") }}', icon: 'bi bi-bookmark', category: 'Content' },
            { name: 'Media Library', url: '{{ route("admin.media.index") }}', icon: 'bi bi-folder', category: 'Content' },
            { name: 'Users', url: '{{ route("admin.users.index") }}', icon: 'bi bi-people', category: 'Users' },
            { name: 'Add User', url: '{{ route("admin.users.create") }}', icon: 'bi bi-person-plus', category: 'Users' },
            { name: 'Roles', url: '{{ route("admin.roles.index") }}', icon: 'bi bi-shield-check', category: 'Users' },
            { name: 'Contacts', url: '{{ route("admin.contacts.index") }}', icon: 'bi bi-envelope', category: 'Users' },
            { name: 'Subscribers', url: '{{ route("admin.subscribers.index") }}', icon: 'bi bi-envelope-paper', category: 'Users' },
            { name: 'Analytics', url: '{{ route("admin.analytics.index") }}', icon: 'bi bi-graph-up', category: 'Reports' },
            { name: 'Settings', url: '{{ route("admin.settings.index") }}', icon: 'bi bi-gear', category: 'System' },
            { name: 'Maintenance', url: '{{ route("admin.maintenance.index") }}', icon: 'bi bi-shield-exclamation', category: 'System' },
            { name: 'Sessions', url: '{{ route("admin.sessions.index") }}', icon: 'bi bi-person-badge', category: 'System' },
            { name: 'Activity Logs', url: '{{ route("admin.activity-logs.index") }}', icon: 'bi bi-clock-history', category: 'System' },
            { name: 'Log Viewer', url: '{{ route("admin.logs.index") }}', icon: 'bi bi-journal-text', category: 'System' },
            { name: 'Backups', url: '{{ route("admin.backup.index") }}', icon: 'bi bi-database', category: 'System' },
            @else
            { name: 'Categories', url: '{{ route("admin.categories.index") }}', icon: 'bi bi-tags', category: 'Browse' },
            @endif
        ],
        get filtered() {
            if (!this.query) return this.pages;
            const q = this.query.toLowerCase();
            return this.pages.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.category.toLowerCase().includes(q)
            );
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.activeIndex = 0;
                this.$nextTick(() => { if (this.$refs.searchInput) this.$refs.searchInput.focus(); });
            }
        },
        close() { this.open = false; },
        navigate() { if (this.filtered[this.activeIndex]) this.go(this.filtered[this.activeIndex]); },
        next() { this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1); },
        prev() { this.activeIndex = Math.max(this.activeIndex - 1, 0); },
        go(item) { window.location.href = item.url; },
        handleKeydown(e) {
            if (e.key === 'Enter') { e.preventDefault(); this.navigate(); }
            else if (e.key === 'ArrowDown') { e.preventDefault(); this.next(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); this.prev(); }
        },
    };
}
</script>
