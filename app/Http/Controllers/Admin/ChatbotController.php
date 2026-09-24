<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotController extends Controller
{
    public function index(Request $request): View
    {
        $query = ChatbotQuery::query()->with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('response', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'faq') {
            $query->whereRaw("response IN (SELECT answer FROM chatbot_faqs)");
        } elseif ($request->input('filter') === 'ai') {
            $query->whereRaw("response NOT IN (SELECT answer FROM chatbot_faqs)");
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $queries = $query->latest()->paginate(30)->withQueryString();

        $stats = [
            'total' => ChatbotQuery::count(),
            'today' => ChatbotQuery::whereDate('created_at', today())->count(),
            'unique_users' => ChatbotQuery::whereNotNull('user_id')->distinct('user_id')->count(),
            'unique_sessions' => ChatbotQuery::distinct('session_id')->count(),
        ];

        $topQuestions = ChatbotQuery::select('message')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('message')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('admin.chatbot.index', compact('queries', 'stats', 'topQuestions'));
    }

    public function destroy(ChatbotQuery $query): RedirectResponse
    {
        $query->delete();
        return back()->with('success', 'Chatbot query deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = ChatbotQuery::query()->with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('response', 'like', "%{$search}%");
            });
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="chatbot-queries-' . now()->format('Y-m-d-His') . '.csv"',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Session', 'Message', 'Response', 'Date']);
            $query->latest()->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->user?->email ?? 'Guest',
                        $row->session_id,
                        $row->message,
                        mb_strimwidth($row->response ?? '', 0, 200, '...'),
                        $row->created_at?->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
