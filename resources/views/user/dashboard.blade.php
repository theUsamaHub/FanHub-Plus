@extends('layouts.user.app')

@section('content')
    <div class="mb-4">
        <h2 class="h4 mb-0 fw-semibold">{{ __('Dashboard') }}</h2>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-check text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0">{{ __("You're logged in!") }}</h5>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ __('Welcome back,') }} {{ Auth::user()->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-tags text-success"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-muted" style="font-size: 0.75rem;">{{ __('Categories') }}</div>
                            <div class="fs-5 fw-semibold">{{ $categoryCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-clock-history text-info"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-muted" style="font-size: 0.75rem;">{{ __('Your Activity') }}</div>
                            <div class="fs-5 fw-semibold">{{ $recentActivityCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">{{ __('Recent Categories') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Slug') }}</th>
                                        <th>{{ __('Created') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCategories as $category)
                                    <tr>
                                        <td class="fw-medium">{{ $category->name }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $category->slug }}</span>
                                        </td>
                                        <td class="text-muted">{{ $category->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">{{ __('No categories yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">{{ __('Quick Actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-person me-2"></i>{{ __('Edit Profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
