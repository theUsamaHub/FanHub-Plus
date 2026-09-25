<?php

namespace Database\Seeders;

use App\Models\ChatChannel;
use Illuminate\Database\Seeder;

class ChatChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            ['name' => 'General', 'slug' => 'general', 'description' => 'Hang out with the community', 'icon' => 'chat', 'sort_order' => 0],
            ['name' => 'Anime', 'slug' => 'anime', 'description' => 'Discuss your favorite anime', 'icon' => 'torii', 'sort_order' => 1],
            ['name' => 'Gaming', 'slug' => 'gaming', 'description' => 'Talk games, share clips', 'icon' => 'controller', 'sort_order' => 2],
            ['name' => 'Movies & TV', 'slug' => 'movies-tv', 'description' => 'Movies, shows, and reviews', 'icon' => 'film', 'sort_order' => 3],
            ['name' => 'Music', 'slug' => 'music', 'description' => 'K-Pop, soundtracks, and more', 'icon' => 'music', 'sort_order' => 4],
        ];

        foreach ($channels as $ch) {
            ChatChannel::updateOrCreate(['slug' => $ch['slug']], $ch);
        }
    }
}
