<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $contents = Content::all();

        if ($users->isEmpty() || $contents->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($users as $uIndex => $user) {
            foreach ($contents as $cIndex => $content) {
                if ($count >= 10) break 2;

                Rating::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'rateable_type' => Content::class,
                        'rateable_id' => $content->id,
                    ],
                    [
                        'rating_type' => ($count % 2 === 0) ? 'star' : 'thumbs',
                        'stars' => ($count % 2 === 0) ? rand(4, 5) : null,
                        'is_thumbs_up' => ($count % 2 !== 0) ? true : null,
                    ]
                );
                $count++;
            }
        }
    }
}
