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

        return view('admin.analytics.index', compact(
            'topContent',
            'topMerchandise',
            'contentByCategory',
            'contentByStatus',
            'userGrowth',
            'reviewVolume',
            'feedbackVolume',
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
}
