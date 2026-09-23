<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Services\ContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending_review');

        $query = Content::with(['category', 'submittedBy'])
            ->where('is_user_submitted', true)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id');

        $submissions = $query->paginate(25)->withQueryString();

        $stats = [
            'pending' => Content::where('is_user_submitted', true)->where('status', 'pending_review')->count(),
            'published' => Content::where('is_user_submitted', true)->where('status', 'published')->count(),
            'rejected' => Content::where('is_user_submitted', true)->where('status', 'rejected')->count(),
            'total' => Content::where('is_user_submitted', true)->count(),
        ];

        return view('admin.submissions.index', compact('submissions', 'stats', 'status'));
    }

    public function show(Content $content): View
    {
        abort_unless($content->is_user_submitted, 404);

        $content->load(['category', 'tags', 'media', 'submittedBy', 'reviewedBy']);

        return view('admin.submissions.show', compact('content'));
    }

    public function approve(Content $content): RedirectResponse
    {
        abort_unless($content->is_user_submitted, 404);

        $this->contentService->setStatus($content, 'published', auth()->id());

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Submission approved and published.');
    }

    public function reject(Content $content): RedirectResponse
    {
        abort_unless($content->is_user_submitted, 404);

        $this->contentService->setStatus($content, 'rejected', auth()->id());

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Submission rejected.');
    }
}
