<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Anime', 'slug' => 'anime', 'description' => 'Anime series, films, and studios'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'description' => 'Video games, esports, and gaming culture'],
            ['name' => 'Movies', 'slug' => 'movies', 'description' => 'Films, franchises, and cinema'],
            ['name' => 'TV Shows', 'slug' => 'tv-shows', 'description' => 'Television series and streaming shows'],
            ['name' => 'K-Pop', 'slug' => 'k-pop', 'description' => 'Korean pop music, groups, and fandoms'],
            ['name' => 'Comics', 'slug' => 'comics', 'description' => 'Western comics and graphic novels'],
            ['name' => 'Manga', 'slug' => 'manga', 'description' => 'Japanese manga and manhua'],
            ['name' => 'Cosplay', 'slug' => 'cosplay', 'description' => 'Cosplay culture, costumes, and events'],
            ['name' => 'Music', 'slug' => 'music', 'description' => 'Soundtracks, OSTs, and idol concerts'],
            ['name' => 'Esports', 'slug' => 'esports', 'description' => 'Competitive tournaments, leagues, and pro players'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
