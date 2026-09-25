<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $logs = $this->query($filters)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'stats' => $this->stats(),
            'filters' => $filters,
            'events' => ActivityLog::EVENTS,
            'types' => $this->logger->auditableTypes(),
            'actors' => ActivityLog::actors(),
            'logger' => $this->logger,
            'hasFilters' => $this->hasFilters($filters),
        ]);
    }

    public function show(ActivityLog $activity_log): View
    {
        $activity_log->load(['user', 'auditable']);

        return view('admin.activity-logs.show', [
            'log' => $activity_log,
            'logger' => $this->logger,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request);
        $logger = $this->logger;

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-logs-'.now()->format('Y-m-d-His').'.csv"',
        ];

        $callback = function () use ($filters, $logger) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Time', 'Actor', 'Event', 'Model', 'Record', 'Summary', 'Changes',
                'IP Address', 'Browser',
            ]);

            $this->query($filters)
                ->latest()
                ->chunk(200, function ($logs) use ($handle, $logger) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at->toDateTimeString(),
                            $log->actor_name,
                            $log->event_label,
                            $log->model_label,
                            $log->subject ?? ('#'.$log->auditable_id),
                            $log->description,
                            $log->summary,
                            $log->ip_address,
                            $log->user_agent,
                            json_encode($log->changes),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(): RedirectResponse
    {
        $count = ActivityLog::count();

        ActivityLog::query()->delete();

        return back()->with('success', trans_choice(
            '{1} :count activity log removed.|[2,*] :count activity logs removed.',
            $count,
            ['count' => $count],
        ));
    }

    /**
     * @return array{search: ?string, event: ?string, type: ?string, user_id: ?int, from: ?string, to: ?string}
     */
    private function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'event' => ['nullable', Rule::in(array_keys(ActivityLog::EVENTS))],
            'type' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [], [], [
            'event' => 'event',
            'user_id' => 'user',
        ]);

        return [
            'search' => $validated['search'] ?? null,
            'event' => $validated['event'] ?? null,
            'type' => $validated['type'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    private function query(array $filters): Builder
    {
        return ActivityLog::query()
            ->with('user')
            ->ofEvent($filters['event'])
            ->ofType($filters['type'])
            ->betweenDates($filters['from'], $filters['to'])
            ->when($filters['user_id'], fn (Builder $q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['search'], function (Builder $q, string $search) {
                $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search)).'%';

                $q->where(function (Builder $inner) use ($term) {
                    $inner->whereRaw("LOWER(COALESCE(subject, '')) LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw('LOWER(auditable_type) LIKE ?', [$term])
                        ->orWhereHas('user', fn (Builder $u) => $u
                            ->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$term])
                            ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$term]));
                });
            });
    }

    /**
     * @return array{total: int, today: int, week: int, events: array<string, int>, types: array<string, int>, actors: array<string, int>}
     */
    private function stats(): array
    {
        $row = DB::table('activity_logs')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when created_at >= ? then 1 end) as today_count', [today()->startOfDay()])
            ->selectRaw('count(case when created_at >= ? then 1 end) as week_count', [now()->subDays(7)])
            ->first();

        $byEvent = DB::table('activity_logs')
            ->select('event', DB::raw('count(*) as aggregate'))
            ->groupBy('event')
            ->pluck('aggregate', 'event');

        $byType = DB::table('activity_logs')
            ->select('auditable_type', DB::raw('count(*) as aggregate'))
            ->whereNotNull('auditable_type')
            ->groupBy('auditable_type')
            ->orderByDesc('aggregate')
            ->limit(6)
            ->pluck('aggregate', 'auditable_type');

        $byActor = DB::table('activity_logs')
            ->select('user_id', DB::raw('count(*) as aggregate'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('aggregate')
            ->limit(5)
            ->pluck('aggregate', 'user_id');

        $topActors = DB::table('users')
            ->whereIn('id', $byActor->keys())
            ->pluck('name', 'id');

        return [
            'total' => (int) $row->total,
            'today' => (int) $row->today_count,
            'week' => (int) $row->week_count,
            'events' => $this->withEventKeys($byEvent),
            'types' => $this->withTypeKeys($byType),
            'actors' => $topActors->map(fn ($name, $id) => [
                'id' => $id,
                'name' => $name,
                'count' => (int) $byActor->get($id),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function withEventKeys($counts): array
    {
        $result = [];

        foreach (array_keys(ActivityLog::EVENTS) as $event) {
            $result[$event] = (int) ($counts[$event] ?? 0);
        }

        return $result;
    }

    /**
     * @return array<string, array{label: string, count: int}>
     */
    private function withTypeKeys($counts): array
    {
        $result = [];

        foreach ($counts as $type => $count) {
            $result[$type] = [
                'label' => $this->logger->modelLabel($type),
                'count' => (int) $count,
            ];
        }

        return $result;
    }

    private function hasFilters(array $filters): bool
    {
        return collect($filters)->filter()->isNotEmpty();
    }
}
