<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $feedbackItems = [
            ['type' => 'suggestion', 'message' => 'Please add dark mode toggle on the main landing page header.', 'status' => 'resolved'],
            ['type' => 'bug', 'message' => 'Video player control overlap on mobile screen sizes.', 'status' => 'open'],
            ['type' => 'query', 'message' => 'How can community creators apply for verified creator badges?', 'status' => 'in_review'],
            ['type' => 'suggestion', 'message' => 'Enable custom playlist creation for soundtrack audio media.', 'status' => 'open'],
            ['type' => 'bug', 'message' => 'Profile picture upload gives 500 error when file is over 2MB.', 'status' => 'resolved'],
            ['type' => 'suggestion', 'message' => 'Add filtering by event location on the global events calendar.', 'status' => 'in_review'],
            ['type' => 'query', 'message' => 'Where can I find official merchandise shipping tracking?', 'status' => 'closed'],
            ['type' => 'suggestion', 'message' => 'Integrate community fan-art voting contests monthly.', 'status' => 'open'],
            ['type' => 'bug', 'message' => 'Bookmark icon state does not update without page refresh.', 'status' => 'resolved'],
            ['type' => 'suggestion', 'message' => 'Add notification alerts for upcoming event ticket pre-orders.', 'status' => 'in_review'],
        ];

        foreach ($feedbackItems as $index => $item) {
            $user = $users[$index % count($users)] ?? null;

            Feedback::create([
                'user_id' => $user?->id,
                'type' => $item['type'],
                'message' => $item['message'],
                'status' => $item['status'],
                'created_at' => now()->subDays(10 - $index),
            ]);
        }
    }
}
