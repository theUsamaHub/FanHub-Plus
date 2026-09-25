<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Rule-based dashboard insights. Deterministic, no LLM.
 */
class DashboardInsightService
{
    /**
     * @return array<int, array{tone: string, title: string, body: string}>
     */
    public function forDashboard(): array
    {
        $insights = [];
        $insights[] = $this->topCategory();
        $insights[] = $this->topContent();
        $insights[] = $this->userMomentum();
        $insights[] = $this->moderationLoad();
        $insights[] = $this->nextEvent();
        $insights[] = $this->feedbackPulse();

        $insights = array_values(array_filter($insights));

        return app(GeminiInsightClient::class)->enrich($insights);
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function topCategory(): ?array
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
            'title' => __(':category is booming', ['category' => $top->category]),
            'body' => __(':views views across :items pieces — strongest category right now.', [
                'views' => number_format((int) $top->views),
                'items' => (int) $top->items,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function topContent(): ?array
    {
        $top = Content::query()
            ->published()
            ->orderByDesc('view_count')
            ->first(['id', 'title', 'view_count', 'type']);

        if (! $top || (int) $top->view_count <= 0) {
            return null;
        }

        return [
            'tone' => 'info',
            'title' => __('This :type is trending', ['type' => $top->type]),
            'body' => __('“:title” leads with :views views.', [
                'title' => \Illuminate\Support\Str::limit($top->title, 48),
                'views' => number_format((int) $top->view_count),
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function userMomentum(): ?array
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
                'title' => __('New guild members arriving'),
                'body' => __(':count joined this week — first wave on this track.', ['count' => $thisWeek]),
            ];
        }

        $delta = (int) round((($thisWeek - $lastWeek) / max(1, $lastWeek)) * 100);

        if ($delta >= 15) {
            return [
                'tone' => 'success',
                'title' => __('User growth is accelerating'),
                'body' => __(':count joined this week, up :delta% from last week.', [
                    'count' => $thisWeek,
                    'delta' => $delta,
                ]),
            ];
        }

        if ($delta <= -15) {
            return [
                'tone' => 'warning',
                'title' => __('Signups cooled this week'),
                'body' => __(':count joined vs :last last week (:delta%).', [
                    'count' => $thisWeek,
                    'last' => $lastWeek,
                    'delta' => $delta,
                ]),
            ];
        }

        return [
            'tone' => 'muted',
            'title' => __('Steady membership flow'),
            'body' => __(':count new members this week (last week: :last).', [
                'count' => $thisWeek,
                'last' => $lastWeek,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function moderationLoad(): ?array
    {
        $pendingSubs = Content::where('is_user_submitted', true)
            ->where('status', 'pending_review')
            ->count();
        $pendingReviews = Review::where('status', 'pending')->count();

        $load = $pendingSubs + $pendingReviews;

        if ($load === 0) {
            return [
                'tone' => 'success',
                'title' => __('Moderation queue is clear'),
                'body' => __('No submissions or reviews waiting. Guild is clean.'),
            ];
        }

        return [
            'tone' => $load >= 10 ? 'danger' : 'warning',
            'title' => __('Moderation needs attention'),
            'body' => __(':subs submissions and :reviews reviews are waiting.', [
                'subs' => $pendingSubs,
                'reviews' => $pendingReviews,
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function nextEvent(): ?array
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
            'title' => __('Next event on the radar'),
            'body' => __(':title (:city) starts in :days day(s).', [
                'title' => $event->title,
                'city' => $event->city,
                'days' => max(0, (int) $days),
            ]),
        ];
    }

    /**
     * @return array{tone: string, title: string, body: string}|null
     */
    private function feedbackPulse(): ?array
    {
        $open = Feedback::where('status', 'open')->count();
        $resolved = Feedback::where('status', 'resolved')->count();

        if ($open >= 5) {
            return [
                'tone' => 'warning',
                'title' => __('Feedback backlog is growing'),
                'body' => __(':open open items — consider a cleanup pass.', ['open' => $open]),
            ];
        }

        if ($open === 0 && $resolved > 0) {
            return [
                'tone' => 'success',
                'title' => __('Fans feel heard'),
                'body' => __(':resolved resolved feedback items and nothing left open.', [
                    'resolved' => $resolved,
                ]),
            ];
        }

        return [
            'tone' => 'muted',
            'title' => __('Feedback pulse'),
            'body' => __(':open open and :resolved resolved.', [
                'open' => $open,
                'resolved' => $resolved,
            ]),
        ];
    }
}
