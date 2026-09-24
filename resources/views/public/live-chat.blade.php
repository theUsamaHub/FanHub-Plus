@extends('layouts.public')

@section('title', 'Live Chat — FanHub Plus')

@section('content')
<section class="fh-live" data-live-chat>
    <div class="fh-live__container">
        {{-- Header --}}
        <div class="fh-live__header">
            <div class="fh-live__header-left">
                <span class="fh-live__dot" aria-hidden="true"></span>
                <h1>Live Chat</h1>
                <span class="fh-live__count" data-live-count>0 online</span>
            </div>
            <p class="fh-live__subtitle">Connect with fans across every universe</p>
        </div>

        {{-- Messages area --}}
        <div class="fh-live__messages" data-live-messages role="log" aria-label="Live chat messages" aria-live="polite">
            @forelse($messages as $msg)
                <div class="fh-live__msg {{ auth()->id() === $msg->user_id ? 'fh-live__msg--mine' : '' }}" data-msg-id="{{ $msg->id }}">
                    <div class="fh-live__msg-avatar">{{ mb_strtoupper(mb_substr($msg->user->name, 0, 1)) }}</div>
                    <div class="fh-live__msg-body">
                        <div class="fh-live__msg-meta">
                            <strong>{{ $msg->user->name }}</strong>
                            <time>{{ $msg->created_at->diffForHumans() }}</time>
                        </div>
                        <p>{{ $msg->message }}</p>
                    </div>
                </div>
            @empty
                <div class="fh-live__empty">
                    <span aria-hidden="true">💬</span>
                    <p>No messages yet. Be the first to say hello!</p>
                </div>
            @endforelse
        </div>

        {{-- Typing indicator --}}
        <div class="fh-live__typing" data-live-typing hidden>
            <span class="fh-live__typing-dot"></span>
            <span class="fh-live__typing-dot"></span>
            <span class="fh-live__typing-dot"></span>
        </div>

        {{-- Input area --}}
        @auth
            <form class="fh-live__form" data-live-form>
                @csrf
                <div class="fh-live__composer">
                    <div class="fh-live__user-badge">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <input type="text" name="message" maxlength="1000" placeholder="Type a message..." autocomplete="off" required data-live-input>
                    <button type="submit" aria-label="Send message" data-live-send>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
                    </button>
                </div>
            </form>
        @else
            <div class="fh-live__guest">
                <div class="fh-live__guest-content">
                    <span class="fh-live__guest-icon" aria-hidden="true">✦</span>
                    <h2>Join the conversation</h2>
                    <p>You need to be logged in to send messages in live chat.</p>
                    <div class="fh-live__guest-actions">
                        <a href="{{ route('login') }}" class="fh-live__btn fh-live__btn--primary">Log in</a>
                        <a href="{{ route('register') }}" class="fh-live__btn fh-live__btn--ghost">Create account</a>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const chat = document.querySelector('[data-live-chat]');
    if (!chat) return;

    const messagesEl = chat.querySelector('[data-live-messages]');
    const form = chat.querySelector('[data-live-form]');
    const input = chat.querySelector('[data-live-input]');
    const typingEl = chat.querySelector('[data-live-typing]');
    let lastId = {{ $messages->last()?->id ?? 0 }};
    let polling = null;

    const scroll = () => { messagesEl.scrollTop = messagesEl.scrollHeight; };

    const addMessage = (msg) => {
        const empty = messagesEl.querySelector('.fh-live__empty');
        if (empty) empty.remove();

        const div = document.createElement('div');
        div.className = 'fh-live__msg' + (msg.mine ? ' fh-live__msg--mine' : '');
        div.dataset.msgId = msg.id;
        div.innerHTML = `
            <div class="fh-live__msg-avatar">${msg.avatar}</div>
            <div class="fh-live__msg-body">
                <div class="fh-live__msg-meta"><strong>${msg.user}</strong><time>${msg.time}</time></div>
                <p>${msg.message.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
            </div>
        `;
        messagesEl.append(div);
        scroll();
    };

    // Poll for new messages
    const poll = async () => {
        try {
            const res = await fetch('{{ route("public.live-chat.fetch") }}?after=' + lastId, {
                headers: { Accept: 'application/json' },
                signal: AbortSignal.timeout(8000),
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.messages.length) {
                data.messages.forEach(msg => {
                    if (msg.id > lastId) {
                        addMessage(msg);
                        lastId = msg.id;
                    }
                });
            }
        } catch {}
    };

    polling = setInterval(poll, 3000);
    scroll();

    // Send message
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            input.disabled = true;
            chat.querySelector('[data-live-send]').disabled = true;

            try {
                const res = await fetch('{{ route("public.live-chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text }),
                    signal: AbortSignal.timeout(10000),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Failed to send');
                addMessage(data);
                lastId = data.id;
                input.value = '';
            } catch (err) {
                alert(err.message);
            } finally {
                input.disabled = false;
                chat.querySelector('[data-live-send]').disabled = false;
                input.focus();
            }
        });
    }

    // Cleanup on leave
    window.addEventListener('beforeunload', () => clearInterval(polling));
})();
</script>
@endpush