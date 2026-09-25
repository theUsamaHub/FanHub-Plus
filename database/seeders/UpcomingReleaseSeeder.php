<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\UpcomingRelease;
use Illuminate\Database\Seeder;

class UpcomingReleaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $releases = [
            ['title' => 'Demon Slayer: Infinity Castle', 'kind' => 'movie', 'slug' => 'demon-slayer-infinity-castle', 'category' => 'anime', 'release_date' => today()->addDays(21), 'release_label' => 'In theaters', 'description' => 'The epic final battle arrives on the big screen.'],
            ['title' => 'GTA VI Launch Window', 'kind' => 'game', 'slug' => 'gta-vi-launch-window', 'category' => 'gaming', 'release_date' => today()->addDays(45), 'release_label' => 'Launch', 'description' => 'Return to the neon-soaked streets of Vice City.'],
            ['title' => 'The Last of Us Season 3', 'kind' => 'series', 'slug' => 'the-last-of-us-season-3', 'category' => 'tv-shows', 'release_date' => today()->addDays(60), 'release_label' => 'Season 3', 'description' => 'Joel and Ellie’s journey continues.'],
            ['title' => 'Avengers: Doomsday', 'kind' => 'movie', 'slug' => 'avengers-doomsday', 'category' => 'movies', 'release_date' => today()->addDays(90), 'release_label' => 'Premiere', 'description' => 'Earth’s mightiest heroes assemble once more.'],
            ['title' => 'Comic-Con International 2027', 'kind' => 'event', 'slug' => 'comic-con-international-2027', 'category' => 'comics', 'release_date' => today()->addDays(120), 'release_label' => 'Convention', 'description' => 'The biggest fandom gathering of the year.'],
            ['title' => 'Jujutsu Kaisen: Cursed Clash DLC', 'kind' => 'game', 'slug' => 'jujutsu-kaisen-cursed-clash-dlc', 'category' => 'anime', 'release_date' => today()->addDays(14), 'release_label' => 'DLC Drop', 'description' => 'New fighters and stages join the roster.'],
        ];

        foreach ($releases as $index => $row) {
            UpcomingRelease::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'kind' => $row['kind'],
                    'category_id' => $categories[$row['category']] ?? null,
                    'release_date' => $row['release_date'],
                    'release_label' => $row['release_label'],
                    'description' => $row['description'],
                    'is_published' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
