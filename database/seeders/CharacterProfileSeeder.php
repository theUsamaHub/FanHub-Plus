<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CharacterProfileSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $media = Media::where('media_type', 'image')->first();

        $characters = [
            ['name' => 'Naruto Uzumaki', 'bio' => 'Hyperactive ninja determined to become the Hokage.'],
            ['name' => 'Son Goku', 'bio' => 'Saiyan warrior defending Earth from powerful threats.'],
            ['name' => 'Monkey D. Luffy', 'bio' => 'Captain of the Straw Hat Pirates pursuing the One Piece.'],
            ['name' => 'Eren Yeager', 'bio' => 'Former Scout Regiment member fighting for Paradis.'],
            ['name' => 'Levi Ackerman', 'bio' => 'Captain of the Special Operations Squad in Scout Regiment.'],
            ['name' => 'Spider-Man (Peter Parker)', 'bio' => 'Friendly neighborhood superhero protecting New York.'],
            ['name' => 'Batman (Bruce Wayne)', 'bio' => 'Dark Knight vigilante protecting Gotham City.'],
            ['name' => 'Iron Man (Tony Stark)', 'bio' => 'Genius billionaire playboy philanthropist hero.'],
            ['name' => 'Pikachu', 'bio' => 'Iconic Electric-type Pokemon companion of Ash Ketchum.'],
            ['name' => 'Mario', 'bio' => 'Heroic plumber saving the Mushroom Kingdom from Bowser.'],
        ];

        foreach ($characters as $index => $char) {
            $category = $categories[$index % count($categories)] ?? $categories->first();

            CharacterProfile::updateOrCreate(
                ['slug' => Str::slug($char['name'])],
                [
                    'category_id' => $category?->id,
                    'name' => $char['name'],
                    'slug' => Str::slug($char['name']),
                    'bio' => $char['bio'],
                    'image_media_id' => $media?->id,
                ]
            );
        }
    }
}
