<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Feedback;
use App\Models\MerchandiseItem;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $topContent = Content::with('category')
            ->orderByDesc('view_count')
            ->take(10)
            ->get();

        $topMerchandise = MerchandiseItem::with('category')
            ->orderByDesc('view_count')
            ->take(10)
            ->get();

        $contentByCategory = Category::withCount('contents')
            ->orderByDesc('contents_count')
            ->get(['id', 'name']);

        $contentByStatus = Content::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $userGrowth = $this->userGrowth();

        $reviewVolume = Review::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $feedbackVolume = Feedback::selectRaw('type, status, count(*) as total')
            ->groupBy('type', 'status')
            ->get()
            ->groupBy('type');

        $ratingsDistribution = Rating::selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('total', 'rating');

        $maxRatingCount = $ratingsDistribution->max() ?? 1;

        // Aggregate metrics
        $totalViews = Content::sum('view_count') + MerchandiseItem::sum('view_count');
        $totalUsers = User::count();
        $newUsers30d = User::where('created_at', '>=', now()->subDays(30))->count();
        $prevPeriodUsers = User::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $engagementRate = $totalViews > 0 ? round(($totalViews / max($totalUsers, 1)) * 100, 1) : 0;

        // Chart data
        $viewsChartData = $this->getViewsChartData();
        $usersChartData = $this->getUsersChartData();

        return view('admin.analytics.index', compact(
            'topContent',
            'topMerchandise',
            'contentByCategory',
            'contentByStatus',
            'userGrowth',
            'reviewVolume',
            'feedbackVolume',
            'ratingsDistribution',
            'maxRatingCount',
            'totalViews',
            'totalUsers',
            'newUsers30d',
            'engagementRate',
            'viewsChartData',
            'usersChartData',
        ));
    }

    private function userGrowth(): array
    {
        $counts = User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->toDateString();
            $days[] = [
                'label' => $date->format('M d'),
                'count' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $days;
    }

    private function getViewsChartData(): array
    {
        // Daily views for last 30 days
        $daily = Content::selectRaw('DATE(created_at) as date, sum(view_count) as views')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('views', 'date');

        $merchDaily = MerchandiseItem::selectRaw('DATE(created_at) as date, sum(view_count) as views')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('views', 'date');

        $labels = [];
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->toDateString();
            $labels[] = $date->format('M d');
            $data[] = (int) (($daily[$key] ?? 0) + ($merchDaily[$key] ?? 0));
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getUsersChartData(): array
    {
        $counts = User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->toDateString();
            $labels[] = $date->format('M d');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
