<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, Category, Content, Feedback, Media};
use App\Services\{FileUploadService, MemberLibrary};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function preferences(Request $request)
    {
        $request->validate(['theme_preference' => ['nullable', Rule::in(['dark', 'light', 'system'])]]);

        return response()->json(['saved' => true]);
    }

    public function bookmarks(Request $request)
    {
        $filters = $request->validate(['type' => ['nullable', Rule::in(array_keys(MemberLibrary::TYPES))]]);
        $bookmarks = $request->user()->bookmarks()->with('bookmarkable')
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('bookmarkable_type', MemberLibrary::TYPES[$type]))
            ->latest()->paginate(12)->withQueryString();
        return view('user.bookmarks', compact('bookmarks', 'filters'));
    }

    public function favorites(Request $request)
    {
        return view('user.favorites', ['categories' => Category::with('iconMedia')->withCount(['contents' => fn ($q) => $q->visibleToPublic()])->orderBy('name')->get(),
            'selected' => $request->user()->favoriteCategories()->pluck('categories.id')->all()]);
    }

    public function activity(Request $request)
    {
        $filters = $request->validate(['type' => 'nullable|in:watched,viewed,saved,favorited,rated,reviewed,submitted']);
        $activity = ActivityLog::where('user_id', $request->user()->id)->where('event', 'like', 'member.%')
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('event', 'member.'.$type))->latest('id')->paginate(20)->withQueryString();
        return view('user.activity', compact('activity', 'filters'));
    }

    public function reviews(Request $request)
    {
        return view('user.reviews', ['reviews' => $request->user()->reviews()->with('reviewable')->latest()->paginate(12)]);
    }

    public function submissions(Request $request)
    {
        return view('user.submissions', ['submissions' => $request->user()->submittedContents()->where('is_user_submitted', true)->with('category')->latest()->paginate(12)]);
    }

    public function create()
    {
        return view('user.submission-form', ['submission' => new Content, 'categories' => Category::orderBy('name')->get()]);
    }

    public function edit(Request $request, Content $submission)
    {
        $this->ownSubmission($request, $submission);
        $submission->load('media');
        return view('user.submission-form', ['submission' => $submission, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request, FileUploadService $uploads, MemberLibrary $library)
    {
        return $this->saveSubmission($request, new Content, $uploads, $library);
    }

    public function update(Request $request, Content $submission, FileUploadService $uploads, MemberLibrary $library)
    {
        $this->ownSubmission($request, $submission);
        return $this->saveSubmission($request, $submission, $uploads, $library);
    }

    private function ownSubmission(Request $request, Content $submission): void
    {
        abort_unless($submission->submitted_by === $request->user()->id && $submission->is_user_submitted, 403);
        abort_if($submission->status === 'published', 403, 'Published submissions are managed by moderators.');
    }

    private function saveSubmission(Request $request, Content $submission, FileUploadService $uploads, MemberLibrary $library)
    {
        $hasCover = $submission->exists && $submission->media()->wherePivot('role', 'cover')->exists();
        $data = $request->validate([
            'title' => 'required|string|max:180', 'category_id' => ['required', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string|min:30|max:50000', 'intent' => 'required|in:draft,submit',
            'cover' => [($hasCover ? 'nullable' : 'required'), 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $data['type'] = $submission->type ?? 'article';
        $cover = null;
        try {
            if ($request->hasFile('cover')) $cover = $uploads->upload($request->file('cover'), 'uploads/submissions', null, $request->user()->id, $data['title']);
            DB::transaction(function () use ($submission, $data, $cover, $request, $library) {
                $submission->fill(collect($data)->only(['title', 'category_id', 'type', 'excerpt', 'body'])->all());
                if (! $submission->exists) $submission->slug = Str::slug($data['title']).'-'.Str::lower(Str::random(10));
                $submission->forceFill(['submitted_by' => $request->user()->id, 'is_user_submitted' => true,
                    'status' => $data['intent'] === 'submit' ? 'pending_review' : 'draft', 'published_at' => null, 'reviewed_by' => null, 'is_featured' => false])->save();
                if ($cover) {
                    $submission->media()->wherePivot('role', 'cover')->detach();
                    $submission->media()->attach($cover->id, ['role' => 'cover', 'sort_order' => 0]);
                }
                if ($data['intent'] === 'submit') $library->activity('submitted', $submission);
            });
        } catch (\Throwable $e) {
            if ($cover) $uploads->delete($cover);
            report($e);
            return back()->withInput()->withErrors(['cover' => 'We could not save your submission. Please try again.']);
        }
        return redirect()->route('user.submissions')->with('success', $data['intent'] === 'submit' ? 'Submitted for moderator approval.' : 'Draft saved. You can finish it any time.');
    }

    public function destroy(Request $request, Content $submission)
    {
        $this->ownSubmission($request, $submission);
        $submission->delete();
        return redirect()->route('user.submissions')->with('success', 'Submission removed.');
    }

    public function feedback(Request $request)
    {
        return view('user.feedback', ['feedback' => $request->user()->feedbacks()->latest()->paginate(8)]);
    }

    public function sendFeedback(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:bug,suggestion,query', 'message' => 'required|string|min:10|max:5000']);
        $request->user()->feedbacks()->create([...$data, 'status' => 'open']);
        return back()->with('success', 'Thanks. Your feedback has been sent to the FanHub team.');
    }
}
