<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\MerchandiseItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MerchandiseItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $media = Media::first();

        $items = [
            ['name' => 'Collector Edition Anime Statue', 'tag' => 'collectible'],
            ['name' => 'Pro Gaming RGB Mechanical Keyboard', 'tag' => 'limited_edition'],
            ['name' => 'Movie Franchise Commemorative Coin Set', 'tag' => 'collectible'],
            ['name' => 'K-Pop Idol Group Lightstick', 'tag' => 'standard'],
            ['name' => 'Vintage Comic Book Art Canvas Print', 'tag' => 'pre_order'],
            ['name' => 'High-Grade Cosplay Foam Weapon Kit', 'tag' => 'limited_edition'],
            ['name' => 'Manga Volume 1 Hardcover Boxset', 'tag' => 'collectible'],
            ['name' => 'Vinyl Soundtrack Record 2LP', 'tag' => 'limited_edition'],
            ['name' => 'Official Esports Team Hoodie', 'tag' => 'standard'],
            ['name' => 'Chibi Plushie Keychain Set', 'tag' => 'pre_order'],
        ];

        foreach ($items as $index => $item) {
            $category = $categories[$index % count($categories)] ?? $categories->first();

            MerchandiseItem::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $category?->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => "Official premium " . $item['name'] . " for dedicated fan collectors.",
                    'image_media_id' => $media?->id,
                    'tag' => $item['tag'],
                    'is_upcoming' => ($index % 3 === 0),
                    'view_count' => 850 + ($index * 50),
                ]
            );
        }
    }
}
