<div class="fh-chat" data-chatbot data-faq-url="{{ route('chatbot.faqs') }}" data-message-url="{{ route('chatbot.message') }}">
    <button class="fh-chat__launcher" type="button" aria-expanded="false" aria-controls="fanhub-chat" aria-label="Open FanHub assistant">
        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 11.5a8 8 0 0 1-8 8H4l-2 2v-10a9 9 0 0 1 18 0Z"/><path d="M7 11h.01M11 11h.01M15 11h.01" stroke-width="3" stroke-linecap="round"/></svg>
        <span>Ask FanHub</span><span class="fh-chat__spark" aria-hidden="true">✦</span>
    </button>
    <section id="fanhub-chat" class="fh-chat__panel" role="dialog" aria-label="FanHub assistant" hidden>
        <header class="fh-chat__header">
            <span class="fh-chat__avatar" aria-hidden="true">✦</span>
            <div><h2>FanHub assistant</h2><p>Your companion across universes</p></div>
            <button class="fh-chat__close" type="button" aria-label="Close chat">×</button>
        </header>
        <div class="fh-chat__body" data-lenis-prevent>
            <div class="fh-chat__welcome"><span class="fh-chat__eyebrow">A LITTLE HELP. A NEW DISCOVERY.</span><h3>Hey, fellow fan <span aria-hidden="true">✧</span></h3><p>Find your next anime, explore a fandom, or ask about FanHub Plus.</p></div>
            <div class="fh-chat__suggestions" aria-label="Suggested questions"></div>
            <div class="fh-chat__messages" role="log" aria-label="Conversation" aria-live="polite" aria-relevant="additions"></div>
            <p class="fh-chat__status" role="status" hidden></p>
            <div class="fh-chat__error" role="alert" hidden><span></span> <button type="button">Retry</button></div>
        </div>
        <form class="fh-chat__form">
            <label class="fh-chat__sr" for="chat-message">Your question</label>
            <div class="fh-chat__composer"><textarea id="chat-message" rows="1" maxlength="1500" placeholder="Ask anything anime…" required></textarea><button type="submit" aria-label="Send message"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 7-7 7 7M12 5v15"/></svg></button></div>
            <p>Powered by Gemini · AI can make mistakes.<br>Chats are saved. AI questions are shared with Google.</p>
        </form>
    </section>
</div>
