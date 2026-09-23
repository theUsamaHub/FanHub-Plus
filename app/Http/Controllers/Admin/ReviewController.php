<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'reviewable']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'total' => Review::count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function show(Review $review): View
    {
        $review->load(['user', 'reviewable']);

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);

        return back()->with('success', 'Review approved.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);

        return back()->with('success', 'Review rejected.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
