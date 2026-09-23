# Authentication & Layout Guide

## Authentication Flow

### Login
1. User submits email + password on `/login`
2. `AuthenticatedSessionController@store` authenticates the user
3. Based on role, redirects to:
   - **Admin** → `route('admin.dashboard')` → `/admin`
   - **User** → `route('user.dashboard')` → `/user/dashboard`

### Login Redirect Logic
```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
$user = $request->user();

if ($user->hasRole('admin')) {
    return redirect()->intended(route('admin.dashboard', absolute: false));
}

return redirect()->intended(route('user.dashboard', absolute: false));
```

### Default Credentials (from seeder)
| Role  | Email            | Password |
|-------|------------------|----------|
| admin | admin@example.com | password |
| user  | user@example.com  | password |

---

## Layout Structure

### File Locations
```
resources/views/layouts/
├── app.blade.php              ← Admin layout (includes admin sidebar)
├── sidebar.blade.php          ← Admin sidebar
├── topbar.blade.php           ← Shared topbar (both roles)
└── user/
    ├── app.blade.php          ← User layout (includes user sidebar)
    └── sidebar.blade.php      ← User sidebar
```

### How Layouts Work
- Both layouts use `@yield('content')` for page content
- Both include `layouts.topbar` (shared)
- Each includes its own sidebar

---

## View Structure

### Admin Views
```php
// resources/views/admin/any-page.blade.php
@extends('layouts.app')

@section('content')
    // page content here
@endsection
```

### User Views
```php
// resources/views/user/any-page.blade.php
@extends('layouts.user.app')

@section('content')
    // page content here
@endsection
```

### Pages Accessible by Both Roles (e.g., Categories)
```php
// resources/views/admin/categories/index.blade.php
@php $layout = auth()->user()->hasRole('admin') ? 'layouts.app' : 'layouts.user.app'; @endphp
@extends($layout)

@section('content')
    // page content here
@endsection
```

---

## Route Structure

### File Locations
```
routes/
├── web.php        ← Main web routes (dashboard, profile)
├── admin.php      ← All admin routes (prefix: /admin)
├── auth.php       ← Login, register, password reset
├── public.php     ← Public pages (about, services, contact)
└── api.php        ← REST API (prefix: /api/v1)
```

### Route Middleware
| Middleware | Used On | Purpose |
|-----------|---------|---------|
| `auth` | Dashboard, Profile, Admin | Requires login |
| `verified` | Dashboard, Profile, Admin | Requires email verification |
| `role:admin` | Admin-only routes | Checks admin role |
| `role:user` | User dashboard | Checks user role |
| `role:admin,user` | Categories (index/show) | Allows both roles |
| `ip-restrict` | All admin routes | IP whitelist |

### User Routes (routes/web.php)
```php
Route::get('/user/dashboard', [User\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:user'])
    ->name('user.dashboard');
```

### Admin Routes (routes/admin.php)
```php
// Both admin + user can access
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'role:admin,user', 'ip-restrict'])
    ->group(function () {
        Route::get('/categories', ...)->name('categories.index');
        Route::get('/categories/{category}', ...)->name('categories.show');
    });

// Admin only
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'role:admin', 'ip-restrict'])
    ->group(function () {
        // All admin CRUD routes here
    });
```

---

## How to Add a New Role

### Step 1: Create the Role in Database
```php
// database/seeders/RoleSeeder.php or tinker
App\Models\Role::create(['name' => 'Editor', 'slug' => 'editor', 'description' => 'Can edit content']);
```

### Step 2: Create Layout Directory
```
resources/views/layouts/editor/
├── app.blade.php      ← Copy from layouts/user/app.blade.php
└── sidebar.blade.php  ← Create with editor-specific links
```

### Step 3: Create Sidebar
```html
<!-- resources/views/layouts/editor/sidebar.blade.php -->
<aside class="sidebar d-none d-lg-flex flex-column" id="sidebar">
    <div class="p-3 border-bottom border-secondary">
        <a href="{{ route('editor.dashboard') }}" class="text-decoration-none d-flex align-items-center">
            <x-application-logo class="w-8 h-8" />
            <span class="text-white fw-semibold ms-2 fs-6">{{ config('app.name', 'FanHubPlus') }}</span>
        </a>
    </div>

    <nav class="flex-grow-1 py-3 overflow-auto">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('editor.dashboard') ? 'active' : '' }}"
                   href="{{ route('editor.dashboard') }}">
                    <i class="bi bi-grid-1x2"></i> {{ __('Dashboard') }}
                </a>
            </li>
            <!-- Add more links as needed -->
        </ul>
    </nav>

    <!-- User info footer (same as other sidebars) -->
</aside>
```

### Step 4: Create Dashboard Controller
```php
// app/Http/Controllers/Editor/DashboardController.php
namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('editor.dashboard');
    }
}
```

### Step 5: Create Dashboard View
```php
// resources/views/editor/dashboard.blade.php
@extends('layouts.editor.app')

@section('content')
    <div class="mb-4">
        <h2 class="h4 mb-0 fw-semibold">{{ __('Editor Dashboard') }}</h2>
    </div>
    <!-- content here -->
@endsection
```

### Step 6: Add Route
```php
// routes/web.php
Route::get('/editor/dashboard', [\App\Http\Controllers\Editor\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:editor'])
    ->name('editor.dashboard');
```

### Step 7: Update Login Redirect
```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
$user = $request->user();

if ($user->hasRole('admin')) {
    return redirect()->intended(route('admin.dashboard', absolute: false));
}

if ($user->hasRole('editor')) {
    return redirect()->intended(route('editor.dashboard', absolute: false));
}

return redirect()->intended(route('user.dashboard', absolute: false));
```

### Step 8: Update Dashboard Redirect
```php
// routes/web.php - /dashboard route
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->user()->hasRole('editor')) {
        return redirect()->route('editor.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
```

---

## Sidebar Navigation

### Admin Sidebar Links
| Section | Link | Route |
|---------|------|-------|
| Dashboard | `/admin` | `admin.dashboard` |
| Profile | `/profile` | `profile.edit` |
| **Content** | | |
| Categories | `/admin/categories` | `admin.categories.index` |
| Recycle Bin | `/admin/categories/trashed` | `admin.categories.trashed` |
| Tags | `/admin/tags` | `admin.tags.index` |
| Media | `/admin/media` | `admin.media.index` |
| **Users** | | |
| Users | `/admin/users` | `admin.users.index` |
| Roles | `/admin/roles` | `admin.roles.index` |
| Contacts | `/admin/contacts` | `admin.contacts.index` |
| Subscribers | `/admin/subscribers` | `admin.subscribers.index` |
| **System** | | |
| Settings | `/admin/settings` | `admin.settings.index` |
| Notifications | `/admin/notifications` | `admin.notifications.index` |
| Activity | `/admin/activity-logs` | `admin.activity-logs.index` |
| Sessions | `/admin/sessions` | `admin.sessions.index` |
| IP Restrictions | `/admin/ip-restrictions` | `admin.ip-restrictions.index` |
| Maintenance | `/admin/maintenance` | `admin.maintenance.index` |
| Health | `/admin/health` | `admin.health.index` |
| Logs | `/admin/logs` | `admin.logs.index` |
| Backup | `/admin/backup` | `admin.backup.index` |

### User Sidebar Links
| Link | Route |
|------|-------|
| Dashboard | `user.dashboard` |
| Profile | `profile.edit` |
| Categories | `admin.categories.index` |

---

## Key Models

### User
```php
$user->hasRole('admin');        // Check single role
$user->hasAnyRole(['admin', 'user']);  // Check multiple roles
$user->assignRole('admin');     // Add role
$user->removeRole('admin');     // Remove role
```

### Role
```php
Role::where('slug', 'admin')->first();
```

---

## Quick Reference

### To make a page admin-only:
1. Route: add `role:admin` middleware
2. View: `@extends('layouts.app')`

### To make a page user-only:
1. Route: add `role:user` middleware
2. View: `@extends('layouts.user.app')`

### To make a page accessible by both:
1. Route: add `role:admin,user` middleware
2. View: use role detection pattern (see above)
