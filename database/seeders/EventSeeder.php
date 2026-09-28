<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::whereIn('slug', [
            'anime', 'gaming', 'movies', 'tv-shows', 'k-pop', 'comics',
            'manga', 'cosplay', 'music', 'esports',
        ])->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->command?->warn('EventSeeder skipped: no categories found. Run CategorySeeder first.');
            return;
        }

        $contentsByCategory = Content::query()
            ->where('status', 'published')
            ->get()
            ->groupBy('category_id');
        $fallbackContent = Content::query()->where('status', 'published')->first();

        $events = [
            [
                'title' => 'Lahore Comic Con 2026',
                'slug' => 'lahore-comic-con-2026',
                'category' => 'comics',
                'event_type' => 'convention',
                'city' => 'Lahore',
                'venue' => 'Expo Centre Lahore',
                'address' => 'Johar Town, Lahore, Punjab',
                'latitude' => 31.4697,
                'longitude' => 74.2728,
                'start_at' => now()->addDays(15)->setTime(10, 0),
                'end_at' => now()->addDays(17)->setTime(20, 0),
                'short_description' => 'Pakistan’s biggest pop-culture convention with cosplay finals, esports demos and creator panels.',
                'description' => 'Three-day celebration of comics, cosplay and creators. Featuring international guests, exclusive drops, indie publisher alley and a dedicated gaming tournament stage.',
                'is_featured' => true,
                'ticket_url' => 'https://example.com/tickets/lahore-comic-con-2026',
                'google_maps_location' => 'https://maps.google.com/?q=Expo+Centre+Lahore',
            ],
            [
                'title' => 'Karachi Anime Film Festival',
                'slug' => 'karachi-anime-film-festival',
                'category' => 'anime',
                'event_type' => 'festival',
                'city' => 'Karachi',
                'venue' => 'Nueplex Cinemas',
                'address' => 'The Place, Karachi',
                'latitude' => 24.8138,
                'longitude' => 67.0299,
                'start_at' => now()->addDays(22)->setTime(13, 0),
                'end_at' => now()->addDays(24)->setTime(22, 0),
                'short_description' => 'Three-day festival of animated classics, premieres and director retrospectives.',
                'description' => 'Screening marathon of award-winning anime films, fan contests, voice actor panels and an exclusive premiere night.',
                'is_featured' => true,
                'ticket_url' => 'https://example.com/tickets/karachi-anime-festival',
                'google_maps_location' => 'https://maps.google.com/?q=Nueplex+Karachi',
            ],
            [
                'title' => 'Islamabad Esports Open Cup',
                'slug' => 'islamabad-esports-open-cup',
                'category' => 'esports',
                'event_type' => 'gaming',
                'city' => 'Islamabad',
                'venue' => 'Pak-China Friendship Centre',
                'address' => 'Garden Avenue, Islamabad',
                'latitude' => 33.7077,
                'longitude' => 73.0498,
                'start_at' => now()->addDays(28)->setTime(11, 0),
                'end_at' => now()->addDays(30)->setTime(23, 0),
                'short_description' => 'Three-day open esports tournament with qualifiers, finals and a community showcase stage.',
                'description' => 'Featuring open qualifiers, playoff bracket and a community showcase stage. Live commentary, broadcast booths and a retro arcade corner round out the weekend.',
                'is_featured' => true,
                'ticket_url' => 'https://example.com/tickets/islamabad-esports-open',
                'google_maps_location' => 'https://maps.google.com/?q=Pak+China+Friendship+Centre',
            ],
            [
                'title' => 'K-Pop World Tour — Encore Screening',
                'slug' => 'kpop-world-tour-encore-screening',
                'category' => 'k-pop',
                'event_type' => 'screening',
                'city' => 'Lahore',
                'venue' => 'CineStar Cinema, Packages Mall',
                'address' => 'Packages Mall, Walton Road, Lahore',
                'latitude' => 31.4520,
                'longitude' => 74.2730,
                'start_at' => now()->addDays(34)->setTime(19, 0),
                'end_at' => now()->addDays(34)->setTime(23, 0),
                'short_description' => 'Big-screen encore screening of the K-Pop World Tour finale night.',
                'description' => 'Watch the closing performance of the latest K-Pop world tour on the largest cinema screen in Lahore, with lightstick giveaways for the first 200 attendees.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/kpop-encore-lahore',
                'google_maps_location' => 'https://maps.google.com/?q=Packages+Mall+Lahore',
            ],
            [
                'title' => 'Manga Creators Workshop',
                'slug' => 'manga-creators-workshop',
                'category' => 'manga',
                'event_type' => 'meetup',
                'city' => 'Karachi',
                'venue' => 'Tech Park Studio',
                'address' => 'Shahrah-e-Faisal, Karachi',
                'latitude' => 24.8710,
                'longitude' => 67.0650,
                'start_at' => now()->addDays(45)->setTime(15, 0),
                'end_at' => now()->addDays(45)->setTime(18, 0),
                'short_description' => 'Hands-on workshop led by manga creators covering inking, panel flow and screentones.',
                'description' => 'Limited-seat workshop with industry creators. Bring your own sketchbook or purchase one at the door. Includes one-on-one feedback sessions.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/manga-workshop',
                'google_maps_location' => null,
            ],
            [
                'title' => 'Cosplay Masquerade Ball',
                'slug' => 'cosplay-masquerade-ball',
                'category' => 'cosplay',
                'event_type' => 'cosplay',
                'city' => 'Lahore',
                'venue' => 'Royal Palm Golf & Country Club',
                'address' => '52-Canal Park, Lahore',
                'latitude' => 31.4711,
                'longitude' => 74.2700,
                'start_at' => now()->addDays(60)->setTime(19, 0),
                'end_at' => now()->addDays(60)->setTime(23, 30),
                'short_description' => 'Black-tie cosplay gala with runway showcase and awards ceremony.',
                'description' => 'Runway showcase of hand-crafted cosplays judged by an international panel. Awards include best craftsmanship, best performance and crowd favourite.',
                'is_featured' => true,
                'ticket_url' => 'https://example.com/tickets/cosplay-ball',
                'google_maps_location' => 'https://maps.google.com/?q=Royal+Palm+Lahore',
            ],
            [
                'title' => 'Superhero Blockbuster Premiere Night',
                'slug' => 'superhero-blockbuster-premiere-night',
                'category' => 'movies',
                'event_type' => 'premiere',
                'city' => 'Islamabad',
                'venue' => 'Centaurus Mega Cinema',
                'address' => 'Fazl-e-Haq Road, Islamabad',
                'latitude' => 33.7077,
                'longitude' => 73.0498,
                'start_at' => now()->addDays(70)->setTime(20, 0),
                'end_at' => now()->addDays(70)->setTime(23, 30),
                'short_description' => 'Red-carpet premiere of the year\'s most-awaited blockbuster with cast meet-and-greet.',
                'description' => 'Red-carpet premiere night featuring meet-and-greet, themed giveaways, photo booth and an after-party with live DJs.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/superhero-premiere',
                'google_maps_location' => 'https://maps.google.com/?q=Centaurus+Mall+Islamabad',
            ],
            [
                'title' => 'Indie Music OST Showcase',
                'slug' => 'indie-music-ost-showcase',
                'category' => 'music',
                'event_type' => 'community',
                'city' => 'Karachi',
                'venue' => 'The Music Room Cafe',
                'address' => 'Clifton, Karachi',
                'latitude' => 24.8150,
                'longitude' => 67.0300,
                'start_at' => now()->addDays(80)->setTime(18, 0),
                'end_at' => now()->addDays(80)->setTime(22, 0),
                'short_description' => 'Live indie showcase of original game and anime soundtracks.',
                'description' => 'A live showcase featuring independent composers performing original game, anime and film soundtracks. Q&A and vinyl pop-up shop included.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/indie-music-ost',
                'google_maps_location' => null,
            ],
            [
                'title' => 'Streaming Series Season Finale Watch Party',
                'slug' => 'streaming-series-season-finale-watch-party',
                'category' => 'tv-shows',
                'event_type' => 'screening',
                'city' => 'Lahore',
                'venue' => 'The Forum Cinema',
                'address' => 'The Forum, Abdul Sattar Edhi Road, Lahore',
                'latitude' => 31.4504,
                'longitude' => 74.2650,
                'start_at' => now()->addDays(90)->setTime(21, 0),
                'end_at' => now()->addDays(90)->setTime(23, 30),
                'short_description' => 'Watch the season finale together on the big screen with themed giveaways.',
                'description' => 'Be the first to watch the season finale on the big screen with surprise giveaways and a post-episode discussion panel.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/watch-party',
                'google_maps_location' => 'https://maps.google.com/?q=The+Forum+Lahore',
            ],
            [
                'title' => 'Gaming Indie Showcase Night',
                'slug' => 'gaming-indie-showcase-night',
                'category' => 'gaming',
                'event_type' => 'community',
                'city' => 'Islamabad',
                'venue' => 'Kuch Khaas',
                'address' => 'F-6 Markaz, Islamabad',
                'latitude' => 33.7100,
                'longitude' => 73.0500,
                'start_at' => now()->addDays(100)->setTime(17, 0),
                'end_at' => now()->addDays(100)->setTime(22, 0),
                'short_description' => 'Hand-picked indie games playable all evening with developer Q&A panels.',
                'description' => 'Try hand-picked indie games, meet the developers and vote for the community favourite.',
                'is_featured' => false,
                'ticket_url' => 'https://example.com/tickets/indie-gaming-night',
                'google_maps_location' => 'https://maps.google.com/?q=Kuch+Khaas+Islamabad',
            ],
            [
                'title' => 'Past Convention Recap — Lahore Comic Con 2025',
                'slug' => 'past-convention-recap-lahore-comic-con-2025',
                'category' => 'comics',
                'event_type' => 'convention',
                'city' => 'Lahore',
                'venue' => 'Expo Centre Lahore',
                'address' => 'Johar Town, Lahore, Punjab',
                'latitude' => 31.4697,
                'longitude' => 74.2728,
                'start_at' => now()->subDays(120)->setTime(10, 0),
                'end_at' => now()->subDays(118)->setTime(20, 0),
                'short_description' => 'Recap and photo gallery from the previous Lahore Comic Con weekend.',
                'description' => 'Full photo recap and after-movie from last year’s convention. Browse the galleries and tag yourself in the community album.',
                'is_featured' => false,
                'ticket_url' => null,
                'google_maps_location' => 'https://maps.google.com/?q=Expo+Centre+Lahore',
            ],
            [
                'title' => 'Past Anime Marathon — Karachi 2026 Spring',
                'slug' => 'past-anime-marathon-karachi-2026-spring',
                'category' => 'anime',
                'event_type' => 'festival',
                'city' => 'Karachi',
                'venue' => 'Nueplex Cinemas',
                'address' => 'The Place, Karachi',
                'latitude' => 24.8138,
                'longitude' => 67.0299,
                'start_at' => now()->subDays(60)->setTime(13, 0),
                'end_at' => now()->subDays(58)->setTime(22, 0),
                'short_description' => 'Spring anime marathon recap featuring the winning cosplay\'s walk-on stage.',
                'description' => 'Recap of the spring anime marathon including the cosplay stage highlight reel and Q&A transcripts.',
                'is_featured' => false,
                'ticket_url' => null,
                'google_maps_location' => null,
            ],
        ];

        // Most events won't have a featured cover image yet — leave the cover
        // media id null so the admin can attach it through the dashboard later.
        foreach ($events as $row) {
            $category = $categories->get($row['category']) ?? $categories->first();
            $content = ($contentsByCategory[$category->id] ?? collect())->first() ?? $fallbackContent;

            // Skip the activity log when seeding so the audit trail isn't
            // polluted with rows the admin has not authored yet.
            Event::withoutEvents(function () use ($row, $category, $content) {
                Event::updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'category_id' => $category->id,
                        'content_id' => $content?->id,
                        'title' => $row['title'],
                        'slug' => $row['slug'],
                        'event_type' => $row['event_type'],
                        'city' => $row['city'],
                        'venue' => $row['venue'] ?? null,
                        'address' => $row['address'] ?? null,
                        'latitude' => $row['latitude'] ?? null,
                        'longitude' => $row['longitude'] ?? null,
                        'start_at' => $row['start_at'],
                        'end_at' => $row['end_at'] ?? null,
                        'short_description' => $row['short_description'] ?? null,
                        'description' => $row['description'] ?? null,
                        'is_featured' => (bool) ($row['is_featured'] ?? false),
                        'popularity_score' => ($row['is_featured'] ?? false) ? 95 : 60,
                        'view_count' => ($row['is_featured'] ?? false) ? 1500 : 250,
                        'status' => 'published',
                        'ticket_url' => $row['ticket_url'] ?? null,
                        'google_maps_location' => $row['google_maps_location'] ?? null,
                        // Per task: skip media — the admin will pick a cover image.
                        'cover_media_id' => null,
                    ],
                );
            });
        }
    }
}