<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $categoryCount = Category::count();

        $recentActivityCount = ActivityLog::where('user_id', $request->user()->id)
            ->whereDate('created_at', today())
            ->count();

        $recentCategories = Category::latest()->take(5)->get();

        return view('user.dashboard', compact('categoryCount', 'recentActivityCount', 'recentCategories'));
    }
}
