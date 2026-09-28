<dialog class="fan-dialog" data-fan-dialog aria-labelledby="fan-dialog-title" data-lenis-prevent>
    <header class="fan-dialog__bar"><h2 id="fan-dialog-title">Fan creation</h2><button type="button" data-fan-close aria-label="Close fan content"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
    <div data-fan-status role="status" class="fan-dialog__status">Loading creation…</div>
    <div data-fan-detail></div>
    <div data-fan-error class="fan-dialog__status" hidden><p>This creation could not be loaded. Please try again.</p><button type="button" class="fh-button" data-fan-retry>Try again</button><a data-fan-fallback href="{{ route('public.fan-content.index') }}">Open full page</a></div>
</dialog>
