<x-auth-shell title="Invalid link." subtitle="This unsubscribe link is invalid or has already been used.">
    <div class="text-center">
        <div class="mb-3 text-danger">
            <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
        </div>
        <p class="mb-4">
            {{ __('This unsubscribe link is invalid or has expired. Please use the link from a recent newsletter email, or manage your preferences from your profile.') }}
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>{{ __('Go Home') }}
        </a>
    </div>
</x-auth-shell>
