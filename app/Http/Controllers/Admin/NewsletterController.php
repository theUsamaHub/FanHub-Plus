<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Newsletter;
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

    public function create(Request $request): View
    {
        return view('admin.newsletters.create', [
            'newsletter' => null,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'selectedCategories' => collect($request->input('categories', []))
                ->map(fn ($id) => (int) $id)
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $filters = $this->validatedFilters($request);

        $newsletter = Newsletter::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'type' => $request->type,
            'reference_id' => $request->reference_id,
            'recipient_filters' => $filters,
            'recipient_count' => $this->newsletterService->getRecipients($filters)->count(),
            'status' => 'draft',
            'sent_by' => auth()->id(),
        ]);

        return redirect()->route('admin.newsletters.show', $newsletter)
            ->with('success', 'Newsletter draft created successfully.');
    }

    public function show(Newsletter $newsletter): View
    {
        $newsletter->load('sender');
        $recipients = $this->newsletterService
            ->getRecipients($newsletter->recipient_filters ?? [])
            ->take(50);

        return view('admin.newsletters.show', compact('newsletter', 'recipients'));
    }

    public function edit(Newsletter $newsletter): View|RedirectResponse
    {
        if ($newsletter->status === 'sent') {
            return redirect()->route('admin.newsletters.show', $newsletter)
                ->with('error', 'Sent newsletters cannot be edited.');
        }

        return view('admin.newsletters.create', [
            'newsletter' => $newsletter,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'selectedCategories' => array_map(
                'intval',
                (array) ($newsletter->recipient_filters['categories'] ?? [])
            ),
        ]);
    }

    public function update(Request $request, Newsletter $newsletter): RedirectResponse
    {
        if ($newsletter->status === 'sent') {
            return back()->with('error', 'Sent newsletters cannot be edited.');
        }

        $filters = $this->validatedFilters($request);

        $newsletter->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'type' => $request->type,
            'reference_id' => $request->reference_id,
            'recipient_filters' => $filters,
            'recipient_count' => $this->newsletterService->getRecipients($filters)->count(),
        ]);

        return redirect()->route('admin.newsletters.show', $newsletter)
            ->with('success', 'Newsletter updated successfully.');
    }

    public function destroy(Newsletter $newsletter): RedirectResponse
    {
        $newsletter->delete();

        return redirect()->route('admin.newsletters.index')
            ->with('success', 'Newsletter deleted.');
    }

    public function send(Request $request, Newsletter $newsletter): RedirectResponse
    {
        if (in_array($newsletter->status, ['sent', 'sending'], true)) {
            return back()->with('error', 'Newsletter already sent.');
        }

        $recipients = $this->newsletterService->getRecipients($newsletter->recipient_filters ?? []);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No active subscribers match these filters.');
        }

        $newsletter->update([
            'status' => 'sending',
            'recipient_count' => $recipients->count(),
        ]);

        $result = $this->newsletterService->send($newsletter, $recipients);

        $newsletter->update([
            'status' => $result['sent'] > 0 ? 'sent' : 'failed',
            'sent_at' => $result['sent'] > 0 ? now() : null,
        ]);

        if ($result['sent'] === 0) {
            return back()->with('error', 'Newsletter could not be sent to any subscriber. Check the mail configuration.');
        }

        $message = 'Newsletter sent to ' . $result['sent'] . ' subscribers.';

        if ($result['failed'] > 0) {
            $message .= ' ' . $result['failed'] . ' failed.';
        }

        return back()->with('success', $message);
    }

    public function preview(Request $request, Newsletter $newsletter): View
    {
        $sampleRecipient = $this->newsletterService
            ->getRecipients($newsletter->recipient_filters ?? [])
            ->first();

        return view('admin.newsletters.preview', compact('newsletter', 'sampleRecipient'));
    }

    private function validatedFilters(Request $request): array
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['nullable', 'in:content,event,character,merchandise,category,custom'],
            'reference_id' => ['nullable', 'integer'],
            'recipient_filters' => ['nullable', 'array'],
            'recipient_filters.categories' => ['nullable', 'array'],
            'recipient_filters.categories.*' => ['integer', 'exists:categories,id'],
        ]);

        return $request->input('recipient_filters', []);
    }
}
