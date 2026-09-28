<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Core genres
            'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Horror',
            'Mystery', 'Romance', 'Sci-Fi', 'Slice of Life', 'Thriller',
            // Anime / manga specific
            'Isekai', 'Shonen', 'Seinen', 'Josei', 'Shojo', 'Mecha', 'Slice of Anime',
            'Magical Girl', 'Supernatural', 'Psychological',
            // Gaming / esports
            'RPG', 'FPS', 'MMO', 'Battle Royale', 'Open World', 'Racing', 'Fighting',
            'Tournament', 'Indie',
            // Movies / TV
            'Superhero', 'Space Opera', 'Cyberpunk', 'Heist', 'Documentary',
            'Limited Series', 'Animation',
            // Music / K-Pop
            'Concert', 'Idol', 'OST', 'Band', 'Hip-Hop', 'EDM',
            // Comics / cosplay / community
            'Variant Cover', 'Indie Comic', 'Costume Build', 'Photoshoot', 'Tutorial',
            'Behind the Scenes', 'Fan Theory', 'Discussion',
            // Mood / meta
            'Family Friendly', 'Mature', 'Nostalgia', 'Beginner Friendly',
        ];

        foreach ($tags as $name) {
            $slug = Str::slug($name);
            Tag::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'slug' => $slug],
            );
        }
    }
}