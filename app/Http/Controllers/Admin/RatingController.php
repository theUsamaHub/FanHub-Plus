<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RatingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Rating::with(['user', 'rateable']);

        if ($type = $request->input('rating_type')) {
            $query->where('rating_type', $type);
        }

        $ratings = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => Rating::count(),
            'star' => Rating::where('rating_type', 'star')->count(),
            'thumbs' => Rating::where('rating_type', 'thumbs')->count(),
            'thumbs_up' => Rating::where('rating_type', 'thumbs')->where('is_thumbs_up', true)->count(),
            'thumbs_down' => Rating::where('rating_type', 'thumbs')->where('is_thumbs_up', false)->count(),
        ];

        $starAverage = Rating::where('rating_type', 'star')->avg('stars');

        return view('admin.ratings.index', compact('ratings', 'stats', 'starAverage'));
    }

    public function destroy(Rating $rating): RedirectResponse
    {
        $rating->delete();

        return redirect()->route('admin.ratings.index')
            ->with('success', 'Rating deleted successfully.');
    }
}
