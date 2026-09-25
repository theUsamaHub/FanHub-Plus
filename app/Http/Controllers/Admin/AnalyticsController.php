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

        $ratingsDistribution = Rating::selectRaw('stars, count(*) as total')
            ->groupBy('stars')
            ->orderBy('stars')
            ->pluck('total', 'stars');

        $maxRatingCount = $ratingsDistribution->max() ?? 1;

        // Aggregate metrics
        $totalViews = Content::sum('view_count') + MerchandiseItem::sum('view_count');
        $totalUsers = User::count();
        $newUsers30d = User::where('created_at', '>=', now()->subDays(30))->count();
        $prevPeriodUsers = User::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $engagementRate = $totalViews > 0 ? round(($totalViews / max($totalUsers, 1)) * 100, 1) : 0;

        // Chart data for different periods
        $viewsChartData = [
            'daily' => $this->getViewsChartData('daily'),
            'weekly' => $this->getViewsChartData('weekly'),
            'monthly' => $this->getViewsChartData('monthly'),
        ];
        $usersChartData = [
            'daily' => $this->getUsersChartData('daily'),
            'weekly' => $this->getUsersChartData('weekly'),
            'monthly' => $this->getUsersChartData('monthly'),
        ];

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

    private function getViewsChartData(string $period = 'daily'): array
    {
        $days = $this->getDaysForPeriod($period);
        
        $query = Content::selectRaw('DATE(created_at) as date, sum(view_count) as views')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('date');

        $merchQuery = MerchandiseItem::selectRaw('DATE(created_at) as date, sum(view_count) as views')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('date');

        if ($period === 'weekly') {
            $query = Content::selectRaw('YEARWEEK(created_at, 1) as week, sum(view_count) as views')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('week');
            $merchQuery = MerchandiseItem::selectRaw('YEARWEEK(created_at, 1) as week, sum(view_count) as views')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('week');
        } elseif ($period === 'monthly') {
            $query = Content::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, sum(view_count) as views')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('month');
            $merchQuery = MerchandiseItem::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, sum(view_count) as views')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('month');
        }

        $contentData = $query->pluck('views', $period === 'daily' ? 'date' : ($period === 'weekly' ? 'week' : 'month'));
        $merchData = $merchQuery->pluck('views', $period === 'daily' ? 'date' : ($period === 'weekly' ? 'week' : 'month'));

        $labels = [];
        $data = [];
        $format = $period === 'daily' ? 'M d' : ($period === 'weekly' ? 'W M d' : 'M Y');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $period === 'daily' ? $date->toDateString() : ($period === 'weekly' ? $date->format('Y\WW') : $date->format('Y-m'));
            $labels[] = $date->format($format);
            $data[] = (int) (($contentData[$key] ?? 0) + ($merchData[$key] ?? 0));
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getUsersChartData(string $period = 'daily'): array
    {
        $days = $this->getDaysForPeriod($period);

        $query = User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('date');

        if ($period === 'weekly') {
            $query = User::selectRaw('YEARWEEK(created_at, 1) as week, count(*) as count')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('week');
        } elseif ($period === 'monthly') {
            $query = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('month');
        }

        $counts = $query->pluck('count', $period === 'daily' ? 'date' : ($period === 'weekly' ? 'week' : 'month'));

        $labels = [];
        $data = [];
        $format = $period === 'daily' ? 'M d' : ($period === 'weekly' ? 'W M d' : 'M Y');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $period === 'daily' ? $date->toDateString() : ($period === 'weekly' ? $date->format('Y\WW') : $date->format('Y-m'));
            $labels[] = $date->format($format);
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getDaysForPeriod(string $period): int
    {
        return match ($period) {
            'daily' => 30,
            'weekly' => 84,
            'monthly' => 365,
            default => 30,
        };
    }
}
