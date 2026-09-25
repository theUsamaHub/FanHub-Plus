@extends('layouts.public')
@section('title', 'Live Chat | FanHub Plus')

@push('styles')
<style>
:root {
    --lc-bg: #2b2d31;
    --lc-surface: #313338;
    --lc-hover: #35373c;
    --lc-active: #404249;
    --lc-text: #dbdee1;
    --lc-muted: #949ba4;
    --lc-accent: #FF922E;
    --lc-input-bg: #383a40;
    --lc-border: #3f4147;
    --lc-online: #23a55a;
    --lc-channel-text: #80848e;
}
.lc-layout { display: flex; height: calc(100vh - 64px); background: var(--lc-bg); color: var(--lc-text); font-family: 'Saira', sans-serif; overflow: hidden; }

/* ── Channel Sidebar ── */
.lc-sidebar { width: 240px; min-width: 240px; background: var(--lc-surface); display: flex; flex-direction: column; border-right: 1px solid var(--lc-border); }
.lc-sidebar-header { padding: 16px; border-bottom: 1px solid var(--lc-border); }
.lc-sidebar-header h2 { margin: 0; font-size: 15px; font-weight: 700; color: #fff; letter-spacing: .3px; }
.lc-sidebar-header p { margin: 4px 0 0; font-size: 11px; color: var(--lc-muted); }
.lc-channels { flex: 1; overflow-y: auto; padding: 8px; }
.lc-channel { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; cursor: pointer; color: var(--lc-channel-text); font-size: 14px; transition: background .15s, color .15s; text-decoration: none; }
.lc-channel:hover { background: var(--lc-hover); color: var(--lc-text); }
.lc-channel.is-active { background: var(--lc-active); color: #fff; }
.lc-channel__icon { width: 20px; text-align: center; font-size: 16px; flex-shrink: 0; }
.lc-channel__name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lc-sidebar-user { padding: 12px 16px; border-top: 1px solid var(--lc-border); display: flex; align-items: center; gap: 10px; background: #232428; }
.lc-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0; }
.lc-user-info { overflow: hidden; }
.lc-user-name { font-size: 13px; font-weight: 600; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lc-user-status { font-size: 11px; color: var(--lc-online); }

/* ── Main Chat Area ── */
.lc-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.lc-chat-header { padding: 14px 20px; border-bottom: 1px solid var(--lc-border); display: flex; align-items: center; gap: 10px; background: var(--lc-surface); }
.lc-chat-header__hash { color: var(--lc-channel-text); font-size: 22px; }
.lc-chat-header__name { font-weight: 700; font-size: 15px; }
.lc-chat-header__desc { font-size: 12px; color: var(--lc-muted); margin-left: 8px; padding-left: 12px; border-left: 1px solid var(--lc-border); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.lc-messages { flex: 1; overflow-y: auto; padding: 16px 20px; display: flex; flex-direction: column; gap: 2px; scroll-behavior: smooth; }
.lc-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--lc-muted); }
.lc-empty__hash { font-size: 64px; margin-bottom: 16px; opacity: .3; }
.lc-empty h3 { margin: 0 0 8px; font-size: 22px; color: #fff; }
.lc-empty p { margin: 0; font-size: 14px; }

.lc-msg { display: flex; gap: 14px; padding: 6px 0; border-radius: 6px; }
.lc-msg:hover { background: var(--lc-hover); }
.lc-msg__avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; color: #fff; flex-shrink: 0; margin-top: 2px; }
.lc-msg__body { flex: 1; min-width: 0; }
.lc-msg__header { display: flex; align-items: baseline; gap: 8px; margin-bottom: 3px; }
.lc-msg__name { font-weight: 600; font-size: 14px; color: #fff; }
.lc-msg__time { font-size: 11px; color: var(--lc-muted); }
.lc-msg__text { font-size: 14px; line-height: 1.5; color: var(--lc-text); word-wrap: break-word; overflow-wrap: anywhere; }

.lc-typing { padding: 4px 20px; font-size: 12px; color: var(--lc-muted); min-height: 24px; }

/* ── Input Area ── */
.lc-input-wrap { padding: 0 20px 20px; }
.lc-input-bar { display: flex; align-items: center; gap: 10px; background: var(--lc-input-bg); border-radius: 10px; padding: 4px 4px 4px 16px; }
.lc-input-bar input { flex: 1; background: none; border: none; outline: none; color: var(--lc-text); font-size: 14px; padding: 10px 0; font-family: 'Saira', sans-serif; }
.lc-input-bar input::placeholder { color: var(--lc-muted); }
.lc-send-btn { width: 40px; height: 40px; border-radius: 8px; border: none; background: var(--lc-accent); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: opacity .2s; flex-shrink: 0; }
.lc-send-btn:hover { opacity: .85; }
.lc-send-btn:disabled { opacity: .4; cursor: default; }

/* ── Members Sidebar ── */
.lc-members { width: 240px; min-width: 240px; background: var(--lc-surface); border-left: 1px solid var(--lc-border); overflow-y: auto; padding: 16px; }
.lc-members__title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--lc-muted); margin-bottom: 12px; }
.lc-member { display: flex; align-items: center; gap: 10px; padding: 6px 8px; border-radius: 6px; margin-bottom: 2px; }
.lc-member:hover { background: var(--lc-hover); }
.lc-member__avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #fff; position: relative; }
.lc-member__dot { position: absolute; bottom: -1px; right: -1px; width: 12px; height: 12px; border-radius: 50%; background: var(--lc-online); border: 3px solid var(--lc-surface); }
.lc-member__name { font-size: 13px; color: var(--lc-text); }

/* ── Responsive ── */
@media(max-width: 1100px) { .lc-members { display: none; } }
@media(max-width: 768px) {
    .lc-sidebar { display: none; }
    .lc-layout.lc-mobile-show-sidebar .lc-sidebar { display: flex; position: fixed; inset: 0; z-index: 100; width: 100%; }
    .lc-mobile-toggle { display: flex !important; }
}
.lc-mobile-toggle { display: none; width: 40px; height: 40px; align-items: center; justify-content: center; border: 1px solid var(--lc-border); border-radius: 8px; background: none; color: var(--lc-text); cursor: pointer; font-size: 18px; }
</style>
@endpush

@section('content')
<div class="lc-layout" x-data="chatApp()" x-init="init()">
    <!-- Channel Sidebar -->
    <aside class="lc-sidebar">
        <div class="lc-sidebar-header">
            <h2>FANHUB+</h2>
            <p>Live Chat</p>
        </div>
        <div class="lc-channels">
            @foreach($channels as $ch)
                <a href="{{ route('chat.index', ['channel' => $ch->slug]) }}"
                   class="lc-channel {{ $activeChannel && $activeChannel->slug === $ch->slug ? 'is-active' : '' }}">
                    <span class="lc-channel__icon">#</span>
                    <span class="lc-channel__name">{{ $ch->name }}</span>
                </a>
            @endforeach
        </div>
        <div class="lc-sidebar-user">
            <div class="lc-avatar" style="background:{{ '#' . substr(md5(auth()->user()->name), 0, 6) }}">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="lc-user-info">
                <div class="lc-user-name">{{ auth()->user()->name }}</div>
                <div class="lc-user-status">Online</div>
            </div>
        </div>
    </aside>

    <!-- Main Chat -->
    <main class="lc-main">
        <div class="lc-chat-header">
            <button class="lc-mobile-toggle" @click="mobileSidebar = !mobileSidebar">☰</button>
            <span class="lc-chat-header__hash">#</span>
            <span class="lc-chat-header__name">{{ $activeChannel?->name ?? 'general' }}</span>
            @if($activeChannel?->description)
                <span class="lc-chat-header__desc">{{ $activeChannel->description }}</span>
            @endif
        </div>

        <div class="lc-messages" id="lc-messages" x-ref="msgContainer">
            @if($messages->isEmpty())
                <div class="lc-empty" x-show="realtimeMessages.length === 0">
                    <div class="lc-empty__hash">#</div>
                    <h3>Welcome to #{{ $activeChannel?->name ?? 'general' }}</h3>
                    <p>This is the start of the conversation. Say something!</p>
                </div>
            @endif
            @foreach($messages as $msg)
                <div class="lc-msg">
                    <div class="lc-msg__avatar" style="background:{{ '#' . substr(md5($msg->user->name), 0, 6) }}">{{ strtoupper(substr($msg->user->name, 0, 2)) }}</div>
                    <div class="lc-msg__body">
                        <div class="lc-msg__header">
                            <span class="lc-msg__name">{{ $msg->user->name }}</span>
                            <span class="lc-msg__time">{{ $msg->created_at->format('g:i A') }}</span>
                        </div>
                        <div class="lc-msg__text">{{ $msg->body }}</div>
                    </div>
                </div>
            @endforeach
            <template x-for="m in realtimeMessages" :key="m.id">
                <div class="lc-msg">
                    <div class="lc-msg__avatar" :style="'background:' + getUserColor(m.user.name)" x-text="getInitials(m.user.name)"></div>
                    <div class="lc-msg__body">
                        <div class="lc-msg__header">
                            <span class="lc-msg__name" x-text="m.user.name"></span>
                            <span class="lc-msg__time" x-text="formatTime(m.created_at)"></span>
                        </div>
                        <div class="lc-msg__text" x-text="m.body"></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="lc-typing" x-show="typingUsers.length" x-text="typingText" x-cloak></div>

        <div class="lc-input-wrap">
            <form class="lc-input-bar" @submit.prevent="sendMessage">
                <input type="text" placeholder="Message #{{ $activeChannel?->name ?? 'general' }}" x-model="messageInput" @input="onTyping" x-ref="chatInput" autocomplete="off">
                <button type="submit" class="lc-send-btn" :disabled="!messageInput.trim()">➤</button>
            </form>
        </div>
    </main>

    <!-- Members Sidebar -->
    <aside class="lc-members">
        <div class="lc-members__title">Online — {{ count($onlineUsers) }}</div>
        @foreach($onlineUsers as $user)
            <div class="lc-member">
                <div class="lc-member__avatar" style="background:{{ '#' . substr(md5($user->name), 0, 6) }}">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                    <span class="lc-member__dot"></span>
                </div>
                <span class="lc-member__name">{{ $user->name }}</span>
            </div>
        @endforeach
    </aside>
</div>

<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
function chatApp() {
    return {
        messageInput: '',
        realtimeMessages: [],
        typingUsers: [],
        mobileSidebar: false,
        messageCounter: 0,

        get typingText() {
            if (this.typingUsers.length === 1) return this.typingUsers[0] + ' is typing...';
            if (this.typingUsers.length > 1) return this.typingUsers.length + ' users typing...';
            return '';
        },

        init() {
            this.scrollDown();
            this.$refs.chatInput?.focus();
            this.setupBroadcast();
        },

        setupBroadcast() {
            if (typeof Pusher === 'undefined') return;
            try {
                this.pusher = new Pusher('{{ env("REVERB_APP_KEY", "fanhubpluskey") }}', {
                    wsHost: '{{ env("REVERB_HOST", "127.0.0.1") }}',
                    wsPort: {{ env("REVERB_PORT", 8080) }},
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }
                });
                const channel = this.pusher.subscribe('private-chat.{{ $activeChannel?->id }}');
                channel.bind('App\\Events\\ChatMessageBroadcast', (data) => {
                    // Avoid duplicate: skip if we already added this message locally
                    if (!this.realtimeMessages.find(m => m.id === data.id)) {
                        this.realtimeMessages.push(data);
                        this.scrollDown();
                    }
                });
            } catch(e) { console.log('Broadcast connection skipped:', e.message); }
        },

        sendMessage() {
            const body = this.messageInput.trim();
            if (!body) return;

            const channelId = {{ $activeChannel?->id ?? 1 }};
            const userId = {{ auth()->id() }};
            const userName = @json(auth()->user()->name);

            // Optimistic: add message instantly
            this.messageCounter++;
            const tempId = 'temp-' + this.messageCounter;
            this.realtimeMessages.push({
                id: tempId, body: body,
                user: { id: userId, name: userName },
                created_at: new Date().toISOString()
            });
            this.messageInput = '';
            this.scrollDown();

            // Send to server
            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ channel_id: channelId, body: body })
            }).then(r => r.json()).then(data => {
                // Replace temp message with real one from server
                const idx = this.realtimeMessages.findIndex(m => m.id === tempId);
                if (idx !== -1) this.realtimeMessages[idx] = data;
            }).catch(err => {
                console.error('Send failed:', err);
            });
        },

        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.msgContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        getInitials(name) { return name ? name.substring(0, 2).toUpperCase() : '??'; },
        getUserColor(name) { return '#' + this.hashCode(name || '').toString(16).slice(0, 6); },
        hashCode(str) { let h = 0; for (let i = 0; i < str.length; i++) h = ((h << 5) - h + str.charCodeAt(i)) | 0; return Math.abs(h); },
        formatTime(iso) { return new Date(iso).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }); },

        onTyping() { /* future: broadcast typing event */ }
    };
}
</script>
@endsection
