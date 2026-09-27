<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Notifications\FeedbackResolvedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::with('user');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($request->has('source') && $request->input('source') !== '') {
            if ($request->input('source') === 'guest') {
                $query->whereNull('user_id');
            } else {
                $query->whereNotNull('user_id');
            }
        }

        $feedback = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'open' => Feedback::where('status', 'open')->count(),
            'in_review' => Feedback::where('status', 'in_review')->count(),
            'resolved' => Feedback::where('status', 'resolved')->count(),
            'closed' => Feedback::where('status', 'closed')->count(),
            'total' => Feedback::count(),
        ];

        return view('admin.feedback.index', compact('feedback', 'stats'));
    }

    public function show(Feedback $feedback): View
    {
        $feedback->load('user');

        return view('admin.feedback.show', compact('feedback'));
    }

    public function updateStatus(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:open,in_review,resolved,closed'],
        ]);

        $feedback->update(['status' => $request->input('status')]);

        if ($feedback->wasChanged('status') && $feedback->status === 'resolved') {
            $this->notifyReporter($feedback);
        }

        return back()->with('success', 'Feedback status updated.');
    }

    private function notifyReporter(Feedback $feedback): void
    {
        $reporter = $feedback->user;

        if (! $reporter?->email) {
            return;
        }

        try {
            $reporter->notify(new FeedbackResolvedNotification($feedback));
        } catch (\Throwable $exception) {
            Log::warning('Could not email the reporter about resolved feedback.', [
                'feedback_id' => $feedback->id,
                'user_id' => $reporter->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }
}
