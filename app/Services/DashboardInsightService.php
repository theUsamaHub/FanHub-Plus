<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Live performance signals for the admin dashboard.
 * Computed from real activity — how the platform is doing right now.
 */
class DashboardInsightService
{
    /**
     * @return array<int, array{tone: string, title: string, body: string}>
     */
    public function forDashboard(): array
    {
        $signals = [];
        $signals[] = $this->categoryMomentum();
        $signals[] = $this->contentEngagement();
        $signals[] = $this->membershipVelocity();
        $signals[] = $this->opsThroughput();
        $signals[] = $this->eventsPipeline();
        $signals[] = $this->fanSentiment();

        return array_values(array_filter($signals));
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function categoryMomentum(): ?array
    {
        $top = Content::query()
            ->join('categories', 'categories.id', '=', 'contents.category_id')
            ->selectRaw('categories.name as category, SUM(contents.view_count) as views, COUNT(*) as items')
            ->groupBy('categories.name')
            ->orderByDesc('views')
            ->first();

        if (! $top || (int) $top->views <= 0) {
            return null;
        }

        return [
            'tone' => 'success',
            'title' => __(':category is carrying the most traffic', ['category' => $top->category]),
            'body' => __(':views views across :items pieces — your strongest lane this period.', [
                'views' => number_format((int) $top->views),
                'items' => (int) $top->items,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function contentEngagement(): ?array
    {
        $top = Content::query()
            ->published()
            ->orderByDesc('view_count')
            ->first(['id', 'title', 'view_count', 'type']);

        $avg = (int) round(Content::published()->avg('view_count') ?: 0);

        if (! $top || (int) $top->view_count <= 0) {
            return null;
        }

        return [
            'tone' => 'info',
            'title' => __('Engagement is peaking on this :type', ['type' => $top->type]),
            'body' => __('“:title” is at :views views (library average :avg).', [
                'title' => \Illuminate\Support\Str::limit($top->title, 40),
                'views' => number_format((int) $top->view_count),
                'avg' => number_format($avg),
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function membershipVelocity(): ?array
    {
        $thisWeek = User::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $lastWeek = User::whereBetween('created_at', [
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek(),
        ])->count();

        if ($lastWeek === 0 && $thisWeek === 0) {
            return null;
        }

        if ($lastWeek === 0) {
            return [
                'tone' => 'success',
                'title' => __('Acquisition just switched on'),
                'body' => __(':count new members this week — first measurable wave.', ['count' => $thisWeek]),
            ];
        }

        $delta = (int) round((($thisWeek - $lastWeek) / max(1, $lastWeek)) * 100);

        if ($delta >= 15) {
            return [
                'tone' => 'success',
                'title' => __('Membership velocity is climbing'),
                'body' => __(':count joins this week — :delta% faster than last week.', [
                    'count' => $thisWeek,
                    'delta' => $delta,
                ]),
            ];
        }

        if ($delta <= -15) {
            return [
                'tone' => 'warning',
                'title' => __('Signups cooled this week'),
                'body' => __(':count joins vs :last last week (:delta%).', [
                    'count' => $thisWeek,
                    'last' => $lastWeek,
                    'delta' => $delta,
                ]),
            ];
        }

        return [
            'tone' => 'muted',
            'title' => __('Steady membership flow'),
            'body' => __(':count new members this week — healthy, stable pace.', [
                'count' => $thisWeek,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function opsThroughput(): ?array
    {
        $pendingSubs = Content::where('is_user_submitted', true)
            ->where('status', 'pending_review')
            ->count();
        $pendingReviews = Review::where('status', 'pending')->count();
        $publishedToday = Content::where('status', 'published')
            ->whereDate('created_at', today())
            ->count();

        $queue = $pendingSubs + $pendingReviews;

        if ($queue === 0) {
            return [
                'tone' => 'success',
                'title' => __('Ops are running clean'),
                'body' => __('Zero backlog — submissions and reviews are fully clear.'),
            ];
        }

        return [
            'tone' => $queue >= 10 ? 'danger' : 'warning',
            'title' => __('Review queue is holding throughput'),
            'body' => __(':subs submissions + :reviews reviews waiting (:pub published today).', [
                'subs' => $pendingSubs,
                'reviews' => $pendingReviews,
                'pub' => $publishedToday,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function eventsPipeline(): ?array
    {
        $event = Event::upcoming()
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->first(['id', 'title', 'city', 'start_at']);

        if (! $event) {
            return null;
        }

        $days = Carbon::now()->startOfDay()->diffInDays($event->start_at->startOfDay(), false);

        return [
            'tone' => 'info',
            'title' => __('Events pipeline is live'),
            'body' => __(':title (:city) goes live in :days day(s).', [
                'title' => $event->title,
                'city' => $event->city,
                'days' => max(0, (int) $days),
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function fanSentiment(): ?array
    {
        $open = Feedback::where('status', 'open')->count();
        $resolved = Feedback::whereIn('status', ['resolved', 'closed'])->count();
        $total = max(1, $open + $resolved);
        $resolutionRate = (int) round(($resolved / $total) * 100);

        if ($open >= 5) {
            return [
                'tone' => 'warning',
                'title' => __('Fan follow-up is lagging'),
                'body' => __(':open still open — resolution rate at :rate%.', [
                    'open' => $open,
                    'rate' => $resolutionRate,
                ]),
            ];
        }

        return [
            'tone' => $open === 0 ? 'success' : 'muted',
            'title' => $open === 0 ? __('Fan follow-up is fully handled') : __('Fan follow-up is under control'),
            'body' => __('Resolution rate :rate% (:open open, :done handled).', [
                'rate' => $resolutionRate,
                'open' => $open,
                'done' => $resolved,
            ]),
        ];
    }
}
