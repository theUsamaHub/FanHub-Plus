<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Media;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $media = Media::first();

        $events = [
            ['title' => 'Tokyo Anime Expo 2026', 'city' => 'Tokyo', 'venue' => 'Big Sight Convention Center', 'event_type' => 'convention', 'is_featured' => true, 'popularity_score' => 98],
            ['title' => 'Global Gaming Championship', 'city' => 'Los Angeles', 'venue' => 'L.A. Live Arena', 'event_type' => 'gaming', 'is_featured' => true, 'popularity_score' => 95],
            ['title' => 'Comic-Con International 2026', 'city' => 'San Diego', 'venue' => 'Convention Center', 'event_type' => 'convention', 'is_featured' => true, 'popularity_score' => 92],
            ['title' => 'K-Pop World Music Festival', 'city' => 'Seoul', 'venue' => 'Olympic Stadium', 'event_type' => 'festival', 'is_featured' => true, 'popularity_score' => 90],
            ['title' => 'International Cosplay Gala', 'city' => 'Paris', 'venue' => 'Palais des Congres', 'event_type' => 'cosplay', 'is_featured' => false, 'popularity_score' => 84],
            ['title' => 'Movie Franchise Fan Summit', 'city' => 'London', 'venue' => 'ExCeL London', 'event_type' => 'premiere', 'is_featured' => false, 'popularity_score' => 80],
            ['title' => 'Manga Creators Expo', 'city' => 'Osaka', 'venue' => 'Intex Osaka', 'event_type' => 'meetup', 'is_featured' => false, 'popularity_score' => 76],
        ];

        foreach ($events as $index => $e) {
            $category = $categories[$index % count($categories)] ?? $categories->first();

            Event::updateOrCreate(
                ['title' => $e['title']],
                [
                    'category_id' => $category?->id,
                    'title' => $e['title'],
                    'description' => "Official " . $e['title'] . " bringing fans together worldwide.",
                    'short_description' => "Join thousands of fans at " . $e['title'] . " in " . $e['city'] . ".",
                    'city' => $e['city'],
                    'venue' => $e['venue'],
                    'address' => "100 Main Street, " . $e['city'],
                    'latitude' => 35.6762 + ($index * 0.1),
                    'longitude' => 139.6503 + ($index * 0.1),
                    'start_at' => now()->addDays($index * 5 + 1),
                    'end_at' => now()->addDays($index * 5 + 3),
                    'ticket_url' => 'https://tickets.example.com/event-' . ($index + 1),
                    'cover_media_id' => $media?->id,
                    'status' => 'published',
                    'event_type' => $e['event_type'],
                    'is_featured' => $e['is_featured'],
                    'popularity_score' => $e['popularity_score'],
                ]
            );
        }
    }
}
