<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

trait RecordsViews
{
    /**
     * Bump the view_count column for this record the first time the
     * current viewer sees it within the dedup window.
     *
     * For authenticated users the key is bound to the user id; for
     * guests it falls back to the session id (or the IP address when
     * there is no active session). The same content re-loaded by the
     * same viewer within the window is treated as a single view.
     */
    public function recordView(): bool
    {
        $viewer = $this->viewCountViewerKey();

        $key = sprintf('view:%s:%d:%s', $this->getTable(), $this->getKey(), $viewer);

        if (Cache::has($key)) {
            return false;
        }

        Cache::put($key, true, now()->addMinutes(30));

        $this->increment('view_count');

        return true;
    }

    /**
     * Produce a stable identifier for the current viewer so the dedup
     * cache key stays unique per person instead of being shared.
     */
    protected function viewCountViewerKey(): string
    {
        if ($userId = auth()->id()) {
            return 'u'.$userId;
        }

        $request = Request::instance();

        if ($request->hasSession()) {
            return 's'.$request->session()->getId();
        }

        return 'i'.($request->ip() ?? 'anon');
    }
}