<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $contents = Content::all();

        if ($users->isEmpty() || $contents->isEmpty()) {
            return;
        }

        $events = ['created', 'updated', 'published', 'bookmarked', 'reviewed', 'rated', 'login', 'profile_update', 'submitted', 'shared'];

        foreach ($events as $index => $eventName) {
            $user = $users[$index % count($users)];
            $content = $contents[$index % count($contents)];

            ActivityLog::create([
                'user_id' => $user->id,
                'event' => $eventName,
                'auditable_type' => Content::class,
                'auditable_id' => $content->id,
                'old_values' => json_encode(['status' => 'draft']),
                'new_values' => json_encode(['status' => 'published']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subHours($index * 4),
            ]);
        }
    }
}
