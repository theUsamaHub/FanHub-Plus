<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $contents = Content::all();

        if ($users->isEmpty() || $contents->isEmpty()) {
            return;
        }

        $reviews = [
            ['title' => 'Absolute Masterpiece!', 'body' => 'Incredible story arc and amazing sound design.'],
            ['title' => 'Worth every second', 'body' => 'Great visuals and excellent character development.'],
            ['title' => 'Solid 9 out of 10', 'body' => 'Engaging storyline that keeps you hooked from start to finish.'],
            ['title' => 'Must watch for all fans', 'body' => 'Captures the essence of the original franchise perfectly.'],
            ['title' => 'Top notch production', 'body' => 'High quality animation and stellar voice acting.'],
            ['title' => 'Highly recommended!', 'body' => 'A standout release that exceeds expectations.'],
            ['title' => 'Fantastic experience', 'body' => 'Brilliant direction and compelling character relationships.'],
            ['title' => 'Exceeded my expectations', 'body' => 'Surpassed all fan theories with an epic season finale.'],
            ['title' => 'Truly iconic', 'body' => 'A instant classic for fandom enthusiasts.'],
            ['title' => 'Superb quality', 'body' => 'Phenomenal artistic execution and detail.'],
        ];

        foreach ($reviews as $index => $r) {
            $user = $users[$index % count($users)];
            $content = $contents[$index % count($contents)];

            Review::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reviewable_type' => Content::class,
                    'reviewable_id' => $content->id,
                ],
                [
                    'title' => $r['title'],
                    'body' => $r['body'],
                    'status' => 'approved',
                ]
            );
        }
    }
}
