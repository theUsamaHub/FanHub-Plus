@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3 text-success">
                        <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-3">{{ __('Unsubscribed Successfully') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('You have been unsubscribed from our newsletter. You will no longer receive email updates from us.') }}
                    </p>
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        <i class="bi bi-house me-1"></i>{{ __('Go Home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection