<?php

namespace Database\Seeders;

use App\Models\ChatbotQuery;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChatbotQuerySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $queries = [
            ['message' => 'Show me top trending anime of 2026', 'response' => 'Here are the top trending anime of 2026 based on popularity scores: 1. Top 10 Must-Watch Anime...'],
            ['message' => 'When is Tokyo Anime Expo 2026 starting?', 'response' => 'Tokyo Anime Expo 2026 starts in Tokyo at Big Sight Convention Center.'],
            ['message' => 'How can I change to dark theme?', 'response' => 'You can change your theme preference in Profile Settings > Appearance.'],
            ['message' => 'Recommend top K-Pop news articles', 'response' => 'Check out "K-Pop World Tour Highlights & Setlists" in our K-Pop section.'],
            ['message' => 'Are there any cosplay events in Paris?', 'response' => 'Yes! The International Cosplay Gala is scheduled in Paris at Palais des Congres.'],
            ['message' => 'Where can I find merchandise statues?', 'response' => 'Explore the Merchandise section for Collector Edition Anime Statues.'],
            ['message' => 'What are the top rated video games right now?', 'response' => 'See our article "Next-Gen Gaming Consoles Complete Breakdown".'],
            ['message' => 'How to submit a fan story?', 'response' => 'Go to Fan Stories and click Submit Your Story.'],
            ['message' => 'Is Comic-Con San Diego tickets available?', 'response' => 'Check the Event details page for Comic-Con International 2026 for ticket link.'],
            ['message' => 'What is the highest rated movie franchise?', 'response' => 'Our feature "Blockbuster Movie Franchise Deep Dive" covers the highest rated franchises.'],
        ];

        foreach ($queries as $index => $q) {
            $user = $users[$index % count($users)] ?? null;

            ChatbotQuery::create([
                'user_id' => $user?->id,
                'session_id' => 'sess_' . Str::random(12),
                'message' => $q['message'],
                'response' => $q['response'],
                'created_at' => now()->subHours(rand(1, 48)),
            ]);
        }
    }
}
