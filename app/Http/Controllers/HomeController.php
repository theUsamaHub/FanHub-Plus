<?php

namespace App\Http\Controllers;

use App\Services\HomepageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, HomepageService $homepage): View
    {
        $filters = $homepage->releaseFilters();
        $activeReleaseFilter = $request->query('release_category', 'all');
        abort_unless(is_string($activeReleaseFilter) && in_array($activeReleaseFilter, ['all', 'merchandise', ...$filters->pluck('slug')->all()], true), 404);

        return view('home.index', [
            ...$homepage->sections(),
            'releases' => $homepage->releases($activeReleaseFilter),
            'releaseFilters' => $filters,
            'activeReleaseFilter' => $activeReleaseFilter,
        ]);
    }
}
