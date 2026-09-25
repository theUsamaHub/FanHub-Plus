<?php

namespace App\Http\Controllers;

use App\Models\MerchandiseItem;
use Illuminate\Http\Request;

class MerchandiseBookmarkController extends Controller
{
    public function __invoke(Request $request, MerchandiseItem $merchandise)
    {
        $data = $request->validate(['saved' => ['required', 'boolean']]);
        $target = ['bookmarkable_type' => $merchandise->getMorphClass(), 'bookmarkable_id' => $merchandise->id];
        if ($data['saved']) {
            $request->user()->bookmarks()->firstOrCreate($target);
        } else {
            $request->user()->bookmarks()->where($target)->delete();
        }
        $message = $data['saved'] ? 'Saved to bookmarks' : 'Removed from bookmarks';

        return $request->expectsJson()
            ? response()->json(['saved' => (bool) $data['saved'], 'message' => $message])
            : back()->with('success', $message);
    }
}
