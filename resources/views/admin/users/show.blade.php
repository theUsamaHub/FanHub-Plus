@extends('layouts.app')

@section('content')
    <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('User Details') }}</h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </div>
    </div>


    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width: 200px;">{{ __('Name') }}</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Email') }}</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Roles') }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-{{ $role->slug === 'admin' ? 'primary' : 'secondary' }}">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Email Verified') }}</td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="badge bg-success">{{ __('Verified') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('Not Verified') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Joined') }}</td>
                                <td>{{ $user->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                            @if ($user->profile)
                                <tr>
                                    <td class="fw-semibold">{{ __('Display name') }}</td>
                                    <td>{{ $user->profile->display_name ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">{{ __('Bio') }}</td>
                                    <td>{{ $user->profile->bio ?: '-' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">{{ __('Favorite categories') }}</td>
                                <td>
                                    @forelse ($user->favoriteCategories as $category)
                                        <span class="badge bg-light text-dark border">{{ $category->name }}</span>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Activity summary') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Submissions') }}</th>
                                <th>{{ __('Bookmarks') }}</th>
                                <th>{{ __('Ratings') }}</th>
                                <th>{{ __('Reviews') }}</th>
                                <th>{{ __('Feedback') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $counts['submissions'] }}</td>
                                <td>{{ $counts['bookmarks'] }}</td>
                                <td>{{ $counts['ratings'] }}</td>
                                <td>{{ $counts['reviews'] }}</td>
                                <td>{{ $counts['feedback'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Recent submissions') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->submittedContents as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                                    <td>{{ $item->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">{{ __('No submissions.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Recent reviews') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->reviews as $review)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($review->title ?: $review->body, 40) }}</td>
                                    <td>{{ ucfirst($review->status) }}</td>
                                    <td>{{ $review->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">{{ __('No reviews.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-danger">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 0.875rem;">
                        {{ __('Deleting this user will permanently remove their account and all associated data.') }}
                    </p>
                    @if ($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('Delete User') }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning py-2 mb-0" style="font-size: 0.875rem;">
                            {{ __('You cannot delete your own account from here.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
