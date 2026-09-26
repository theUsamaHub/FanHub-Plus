<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscriber::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'active') {
            $query->active();
        } elseif ($request->input('filter') === 'unsubscribed') {
            $query->whereNotNull('unsubscribed_at');
        } elseif ($request->input('filter') === 'bounced') {
            $query->where('status', 'bounced');
        } elseif ($request->input('filter') === 'complained') {
            $query->where('status', 'complained');
        }

        if ($from = $request->input('from')) {
            $query->whereDate('subscribed_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('subscribed_at', '<=', $to);
        }

        $subscribers = $query->latest()->paginate(30);

        $subscriberCounts = Subscriber::selectRaw("count(*) as total")
            ->selectRaw("count(case when status = 'active' then 1 end) as active_count")
            ->selectRaw("count(case when status = 'unsubscribed' then 1 end) as unsubscribed_count")
            ->selectRaw("count(case when status = 'bounced' then 1 end) as bounced_count")
            ->selectRaw("count(case when status = 'complained' then 1 end) as complained_count")
            ->first();

        $stats = [
            'total' => $subscriberCounts->total,
            'active' => $subscriberCounts->active_count,
            'unsubscribed' => $subscriberCounts->unsubscribed_count,
            'bounced' => $subscriberCounts->bounced_count,
            'complained' => $subscriberCounts->complained_count,
        ];

        return view('admin.subscribers.index', compact('subscribers', 'stats'));
    }

    public function show(Subscriber $subscriber): View
    {
        $subscriber->load(['preferences']);
        
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $preferences = $subscriber->getPreferences();
        $selectedCategories = $preferences['categories'] ?? [];

        return view('admin.subscribers.show', compact('subscriber', 'categories', 'selectedCategories'));
    }

    public function edit(Subscriber $subscriber): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $preferences = $subscriber->getPreferences();
        $selectedCategories = $preferences['categories'] ?? [];

        return view('admin.subscribers.edit', compact('subscriber', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Subscriber $subscriber): RedirectResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,unsubscribed,bounced,complained'],
            'preferences.categories' => ['nullable', 'array'],
            'preferences.categories.*' => ['integer', 'exists:categories,id'],
        ]);

        $subscriber->update([
            'name' => $request->name,
            'status' => $request->status,
            'preferences' => $request->input('preferences', []),
        ]);

        return redirect()->route('admin.subscribers.show', $subscriber)
            ->with('success', 'Subscriber updated successfully.');
    }

    public function updateStatus(Request $request, Subscriber $subscriber): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:active,unsubscribed,bounced,complained'],
        ]);

        $subscriber->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'unsubscribed' && !$subscriber->unsubscribed_at) {
            $subscriber->update(['unsubscribed_at' => now()]);
        } elseif ($request->status === 'active' && $subscriber->unsubscribed_at) {
            $subscriber->update(['unsubscribed_at' => null, 'subscribed_at' => $subscriber->subscribed_at ?? now()]);
        }

        return back()->with('success', 'Subscriber status updated.');
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete,activate,unsubscribe,bounce,complain'],
            'subscriber_ids' => ['required', 'array'],
            'subscriber_ids.*' => ['integer', 'exists:subscribers,id'],
        ]);

        $subscribers = Subscriber::whereIn('id', $request->subscriber_ids)->get();
        $count = $subscribers->count();

        match ($request->action) {
            'delete' => $subscribers->each->delete(),
            'activate' => $subscribers->each->update(['status' => 'active', 'unsubscribed_at' => null]),
            'unsubscribe' => $subscribers->each->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]),
            'bounce' => $subscribers->each->update(['status' => 'bounced']),
            'complain' => $subscribers->each->update(['status' => 'complained']),
        };

        return back()->with('success', "Bulk action '{$request->action}' applied to {$count} subscriber(s).");
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Subscriber::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'unsubscribed') {
            $query->whereNotNull('unsubscribed_at');
        } elseif ($request->input('filter') === 'active') {
            $query->active();
        } elseif ($request->input('filter') === 'bounced') {
            $query->where('status', 'bounced');
        } elseif ($request->input('filter') === 'complained') {
            $query->where('status', 'complained');
        }

        if ($from = $request->input('from')) {
            $query->whereDate('subscribed_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('subscribed_at', '<=', $to);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subscribers-' . now()->format('Y-m-d-His') . '.csv"',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Name', 'Status', 'Subscribed At', 'IP Address', 'Categories']);

            $query->latest()->chunk(200, function ($subscribers) use ($handle) {
                foreach ($subscribers as $subscriber) {
                    $prefs = $subscriber->getPreferences();
                    $catNames = '';
                    if (!empty($prefs['categories'])) {
                        $cats = Category::whereIn('id', $prefs['categories'])->pluck('name')->toArray();
                        $catNames = implode(', ', $cats);
                    }

                    fputcsv($handle, [
                        $subscriber->email,
                        $subscriber->name,
                        $subscriber->status,
                        $subscriber->subscribed_at?->toDateTimeString(),
                        $subscriber->ip_address,
                        $catNames,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}