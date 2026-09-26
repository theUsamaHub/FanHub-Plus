<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\CharacterProfile;
use App\Models\MerchandiseItem;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Services\NewsletterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterService $newsletterService
    ) {}

    public function index(Request $request): View
    {
        $query = Newsletter::with('sender')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $newsletters = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Newsletter::count(),
            'sent' => Newsletter::where('status', 'sent')->count(),
            'draft' => Newsletter::where('status', 'draft')->count(),
            'sending' => Newsletter::where('status', 'sending')->count(),
        ];

        return view('admin.newsletters.index', compact('newsletters', 'stats'));
    }

    public function create(): View
    {
        return view('admin.newsletters.create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['nullable', 'in:content,event,character,merchandise,category,custom'],
            'reference_id' => ['nullable', 'integer'],
            'recipient_filters' => ['nullable', 'array'],
            'recipient_filters.categories' => ['nullable', 'array'],
            'recipient_filters.status' => ['nullable', 'in:active,unsubscribed'],
        ]);

        $filters = $request->input('recipient_filters', []);
        $recipients = $this->getRecipients($filters);

        $newsletter = Newsletter::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'type' => $request->type,
            'reference_id' => $request->reference_id,
            'recipient_filters' => $filters,
            'recipient_count' => $recipients->count(),
            'status' => 'draft',
            'sent_by' => auth()->id(),
        ]);

        return redirect()->route('admin.newsletters.show', $newsletter)
            ->with('success', 'Newsletter draft created successfully.');
    }

    public function show(Newsletter $newsletter): View
    {
        $newsletter->load('sender');
        $filters = $newsletter->recipient_filters ?? [];
        $recipients = $this->getRecipients($filters)->take(50);

        return view('admin.newsletters.show', compact('newsletter', 'recipients'));
    }

    public function send(Request $request, Newsletter $newsletter): RedirectResponse
    {
        if ($newsletter->status === 'sent') {
            return back()->with('error', 'Newsletter already sent.');
        }

        $filters = $newsletter->recipient_filters ?? [];
        $recipients = $this->getRecipients($filters);

        $newsletter->update([
            'status' => 'sending',
            'recipient_count' => $recipients->count(),
        ]);

        $this->newsletterService->send($newsletter, $recipients);

        $newsletter->refresh();
        $newsletter->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $newsletter->sent_count,
            'failed_count' => $newsletter->failed_count,
        ]);

        return back()->with('success', 'Newsletter sent to ' . $newsletter->sent_count . ' subscribers.');
    }

    public function preview(Request $request, Newsletter $newsletter): View
    {
        $filters = $newsletter->recipient_filters ?? [];
        $sampleRecipient = $this->getRecipients($filters)->first();

        return view('admin.newsletters.preview', compact('newsletter', 'sampleRecipient'));
    }

    private function getRecipients(array $filters)
    {
        $query = Subscriber::query()->where('status', 'active');

        if (!empty($filters['categories'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['categories'] as $categoryId) {
                    $q->orWhereJsonContains('preferences->categories', $categoryId);
                }
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'active') {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
}