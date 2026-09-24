<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();
        $user = User::where('email', 'user@example.com')->first() ?? User::first();

        // 1. Upcoming Content (10 Items) - Future release dates
        $upcomingContents = [
            ['title' => 'Upcoming Anime Movie Teaser Trailer 2027', 'type' => 'video', 'excerpt' => 'Sneak peek at the upcoming theatrical anime release.'],
            ['title' => 'Next-Gen VR MMORPG Launch Announcement', 'type' => 'article', 'excerpt' => 'Revolutionary VR MMORPG set to release next season.'],
            ['title' => 'Sci-Fi Film Sequel Teaser & Cast Reveal', 'type' => 'article', 'excerpt' => 'New cast members revealed for upcoming sci-fi blockbuster.'],
            ['title' => 'Upcoming K-Pop Comeback Album Tracklist', 'type' => 'article', 'excerpt' => 'Official tracklist drop for the anticipated autumn album.'],
            ['title' => 'Comic Book Crossover Event 2027 Preview', 'type' => 'image', 'excerpt' => 'First look artwork from the upcoming massive hero crossover.'],
            ['title' => 'Upcoming Dark Fantasy Manga Adaption Premiere', 'type' => 'article', 'excerpt' => 'Highly anticipated dark fantasy series premiering soon.'],
            ['title' => 'Annual International Cosplay Expo Preview', 'type' => 'article', 'excerpt' => 'What to expect at the upcoming international cosplay showcase.'],
            ['title' => 'Upcoming Cyberpunk OST Vinyl Boxset', 'type' => 'audio', 'excerpt' => 'Pre-orders opening soon for collector edition soundtrack.'],
            ['title' => 'Esports World Cup 2027 Qualified Teams', 'type' => 'article', 'excerpt' => 'Complete list of teams qualifying for upcoming global finals.'],
            ['title' => 'Upcoming Streaming Series Season 2 Teaser', 'type' => 'video', 'excerpt' => 'Trailer reaction and release timeline for season 2.'],
        ];

        foreach ($upcomingContents as $index => $item) {
            $category = $categories[$index % count($categories)] ?? $categories->first();
            Content::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'category_id' => $category?->id,
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['excerpt'] . " Stay tuned for full release details and community updates.",
                    'release_date' => now()->addDays(($index + 1) * 7),
                    'popularity_score' => 80 + $index,
                    'view_count' => 500 + ($index * 50),
                    'status' => 'published',
                    'is_featured' => false,
                    'is_user_submitted' => false,
                    'submitted_by' => $admin?->id,
                    'reviewed_by' => $admin?->id,
                    'published_at' => now(),
                ]
            );
        }

        // 2. Trending Now Content (7 Items) - High popularity score & featured
        $trendingContents = [
            ['title' => 'Top 10 Must-Watch Anime of the Season', 'type' => 'article', 'excerpt' => 'Discover the best trending anime releases taking fans by storm.'],
            ['title' => 'Next-Gen Gaming Consoles Complete Breakdown', 'type' => 'article', 'excerpt' => 'In-depth benchmark analysis of next-generation console hardware.'],
            ['title' => 'Blockbuster Movie Franchise Deep Dive & Lore', 'type' => 'article', 'excerpt' => 'Unpacking decades of cinematic universe easter eggs.'],
            ['title' => 'K-Pop World Tour Record Breaking Ticket Sales', 'type' => 'article', 'excerpt' => 'Historic ticket sale records set across global stadium venues.'],
            ['title' => 'World Cosplay Championship Winning Designs', 'type' => 'video', 'excerpt' => 'Stunning costume craftsmanship and winner interviews.'],
            ['title' => 'Esports Global Finals Champion Recap', 'type' => 'article', 'excerpt' => 'Grand finals recap and MVP play highlights.'],
            ['title' => 'Iconic Anime Original Soundtracks Tier List', 'type' => 'audio', 'excerpt' => 'Definitive community ranking of iconic orchestral OSTs.'],
        ];

        foreach ($trendingContents as $index => $item) {
            $category = $categories[$index % count($categories)] ?? $categories->first();
            Content::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'category_id' => $category?->id,
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['excerpt'] . " Full detailed trending coverage for FanHub community.",
                    'release_date' => now()->subDays($index * 2),
                    'popularity_score' => 98 - ($index * 1),
                    'view_count' => 15000 - ($index * 1200),
                    'status' => 'published',
                    'is_featured' => true,
                    'is_user_submitted' => false,
                    'submitted_by' => $admin?->id,
                    'reviewed_by' => $admin?->id,
                    'published_at' => now()->subDays($index * 2),
                ]
            );
        }

        // 3. Fan Stories (4 Items) - User submitted articles
        $stories = [
            ['title' => 'My First Time Attending Tokyo Anime Expo: A Fan Memoir', 'excerpt' => 'An unforgettable journey through Akihabara and Tokyo Big Sight.'],
            ['title' => 'How Building Cosplay Props Changed My Creative Life', 'excerpt' => 'From EVA foam beginners tutorials to grand stage competition.'],
            ['title' => 'Ten Years in a Gaming Guild: Friendship Beyond Screens', 'excerpt' => 'Reflecting on a decade of raids, victories, and real-life meetups.'],
            ['title' => 'Collecting Vintage Manga: The Hunt for Rare First Editions', 'excerpt' => 'Tips and stories from 15 years of dedicated manga collecting.'],
        ];

        foreach ($stories as $index => $item) {
            $category = $categories[$index % count($categories)] ?? $categories->first();
            Content::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'category_id' => $category?->id,
                    'title' => $item['title'],
                    'type' => 'article',
                    'excerpt' => $item['excerpt'],
                    'body' => $item['excerpt'] . " Community story submitted by dedicated fan hub members.",
                    'release_date' => now()->subDays(10 + $index * 5),
                    'popularity_score' => 88 - $index,
                    'view_count' => 3200 + ($index * 400),
                    'status' => 'published',
                    'is_featured' => false,
                    'is_user_submitted' => true,
                    'submitted_by' => $user?->id,
                    'reviewed_by' => $admin?->id,
                    'published_at' => now()->subDays(10 + $index * 5),
                ]
            );
        }
    }
}
