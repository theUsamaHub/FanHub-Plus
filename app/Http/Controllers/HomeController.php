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

        $activeMerchFilter = $request->query('merch_category', 'all');
        abort_unless(is_string($activeMerchFilter) && in_array($activeMerchFilter, ['all', ...array_keys(config('fandoms'))], true), 404);
        $merchandise = $homepage->merchandise($activeMerchFilter);
        $savedMerchandise = $request->user()?->bookmarks()
            ->where('bookmarkable_type', (new \App\Models\MerchandiseItem)->getMorphClass())
            ->pluck('bookmarkable_id')->all() ?? [];
        $merchData = compact('merchandise', 'savedMerchandise', 'activeMerchFilter');
        if ($request->header('X-Home-Section') === 'merchandise') {
            return view('home.sections.merchandise-results', $merchData);
        }

        return view('home.index', [
            ...$homepage->sections(),
            ...$merchData,
            'releases' => $homepage->releases($activeReleaseFilter),
            'releaseFilters' => $filters,
            'activeReleaseFilter' => $activeReleaseFilter,
        ]);
    }
}
