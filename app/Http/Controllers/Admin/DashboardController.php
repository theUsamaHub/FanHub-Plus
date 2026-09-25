<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ChatbotQuery;
use App\Models\Content;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->getStats();
        $chartData = $this->getChartData();
        $activityToday = $this->getActivityToday();

        $recentSubmissions = Content::with(['submittedBy', 'category'])
            ->where('is_user_submitted', true)
            ->latest()
            ->take(6)
            ->get();

        $pendingReviews = Review::with(['user', 'reviewable'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentFeedback = Feedback::with('user')
            ->latest()
            ->take(6)
            ->get();

        $upcomingEvents = Event::upcoming()
            ->where('status', '!=', 'cancelled')
            ->take(5)
            ->get();

        $popularContent = Content::with('category')
            ->published()
            ->orderByDesc('view_count')
            ->take(5)
            ->get();

        $contentByStatus = Content::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $usersByRole = DB::table('users')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->groupBy('roles.name')
            ->selectRaw("COALESCE(roles.name, 'No role') as role, COUNT(*) as total")
            ->orderByDesc('total')
            ->pluck('total', 'role');

        $contentByType = Content::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->pluck('total', 'type');

        $chatbotTopQuestions = ChatbotQuery::select('message')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('message')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'label' => \Illuminate\Support\Str::limit($row->message, 42),
                'count' => (int) $row->count,
            ])
            ->values();

        $reviewsByStatus = Review::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $chartPayload = [
            'growth' => collect($chartData)->map(fn ($d) => [
                'label' => $d['label'],
                'users' => (int) $d['users'],
            ])->values(),
            'usersByRole' => $usersByRole->map(fn ($v, $k) => [
                'label' => (string) $k,
                'count' => (int) $v,
            ])->values(),
            'contentByType' => $contentByType->map(fn ($v, $k) => [
                'label' => (string) $k,
                'count' => (int) $v,
            ])->values(),
            'contentByStatus' => collect($contentByStatus)->map(fn ($v, $k) => [
                'label' => ucwords(str_replace('_', ' ', (string) $k)),
                'count' => (int) $v,
            ])->values(),
            'chatbotTopQuestions' => $chatbotTopQuestions,
            'reviewsByStatus' => collect($reviewsByStatus)->map(fn ($v, $k) => [
                'label' => (string) $k,
                'count' => (int) $v,
            ])->values(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'activityToday',
            'recentSubmissions',
            'pendingReviews',
            'recentFeedback',
            'upcomingEvents',
            'popularContent',
            'contentByStatus',
            'chartPayload',
        ));
    }

    private function getStats(): array
    {
        return [
            [
                'label' => __('Total Users'),
                'count' => User::count(),
                'icon' => 'bi-people',
                'color' => 'primary',
                'route' => 'admin.users.index',
            ],
            [
                'label' => __('Total Content'),
                'count' => Content::count(),
                'icon' => 'bi-file-earmark-text',
                'color' => 'success',
                'route' => 'admin.contents.index',
            ],
            [
                'label' => __('Published Content'),
                'count' => Content::where('status', 'published')->count(),
                'icon' => 'bi-check-circle',
                'color' => 'info',
                'route' => 'admin.contents.index',
                'params' => ['status' => 'published'],
            ],
            [
                'label' => __('Pending Submissions'),
                'count' => Content::where('is_user_submitted', true)->where('status', 'pending_review')->count(),
                'icon' => 'bi-inbox',
                'color' => 'warning',
                'route' => 'admin.submissions.index',
            ],
            [
                'label' => __('Total Categories'),
                'count' => Category::count(),
                'icon' => 'bi-tags',
                'color' => 'secondary',
                'route' => 'admin.categories.index',
            ],
            [
                'label' => __('Upcoming Events'),
                'count' => Event::upcoming()->where('status', 'published')->count(),
                'icon' => 'bi-calendar-event',
                'color' => 'primary',
                'route' => 'admin.events.index',
                'params' => ['status' => 'published', 'upcoming' => '1'],
            ],
            [
                'label' => __('Pending Reviews'),
                'count' => Review::where('status', 'pending')->count(),
                'icon' => 'bi-chat-left-text',
                'color' => 'warning',
                'route' => 'admin.reviews.index',
                'params' => ['status' => 'pending'],
            ],
            [
                'label' => __('Open Feedback'),
                'count' => Feedback::where('status', 'open')->count(),
                'icon' => 'bi-megaphone',
                'color' => 'danger',
                'route' => 'admin.feedback.index',
                'params' => ['status' => 'open'],
            ],
            [
                'label' => __('Total Media'),
                'count' => Media::count(),
                'icon' => 'bi-folder',
                'color' => 'dark',
                'route' => 'admin.media.index',
            ],
        ];
    }

    private function getChartData(): array
    {
        $userCounts = User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $dateKey = $date->toDateString();
            $days[] = [
                'label' => $date->format('M d'),
                'users' => (int) ($userCounts[$dateKey] ?? 0),
            ];
        }

        return $days;
    }

    private function getActivityToday(): array
    {
        $events = ActivityLog::selectRaw('event, count(*) as count')
            ->whereDate('created_at', today())
            ->groupBy('event')
            ->pluck('count', 'event');

        $total = $events->sum();

        return [
            'total' => $total,
            'created' => (int) ($events['created'] ?? 0),
            'updated' => (int) ($events['updated'] ?? 0),
            'deleted' => (int) ($events['deleted'] ?? 0),
        ];
    }
}
