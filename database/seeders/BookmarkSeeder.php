<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Content;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $contents = Content::all();
        $events = Event::all();

        if ($users->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($users as $uIndex => $user) {
            // Bookmark a content
            if (isset($contents[$uIndex])) {
                Bookmark::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'bookmarkable_type' => Content::class,
                        'bookmarkable_id' => $contents[$uIndex]->id,
                    ],
                    [
                        'note' => 'Saved for weekend reading #' . ($count + 1),
                    ]
                );
                $count++;
            }

            if ($count >= 10) break;

            // Bookmark an event
            if (isset($events[$uIndex])) {
                Bookmark::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'bookmarkable_type' => Event::class,
                        'bookmarkable_id' => $events[$uIndex]->id,
                    ],
                    [
                        'note' => 'Attending convention #' . ($count + 1),
                    ]
                );
                $count++;
            }

            if ($count >= 10) break;
        }
    }
}
