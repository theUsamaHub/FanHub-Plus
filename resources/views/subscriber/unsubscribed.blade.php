<x-auth-shell title="Unsubscribed." subtitle="You have been unsubscribed from our newsletter.">
    <div class="text-center">
        <div class="mb-3 text-success">
            <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
        </div>
        <p class="mb-4">
            {{ __('You will no longer receive email updates from us. You can re-subscribe any time from the home page or your profile.') }}
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>{{ __('Go Home') }}
        </a>
    </div>
</x-auth-shell>
