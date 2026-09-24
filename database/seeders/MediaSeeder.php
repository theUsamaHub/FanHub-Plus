<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();

        $mediaItems = [
            ['path' => 'media/hero_banner.jpg', 'original_filename' => 'hero_banner.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'alt_text' => 'Hero Banner Image'],
            ['path' => 'media/anime_poster.jpg', 'original_filename' => 'anime_poster.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'alt_text' => 'Anime Key Visual Poster'],
            ['path' => 'media/gaming_setup.png', 'original_filename' => 'gaming_setup.png', 'media_type' => 'image', 'mime_type' => 'image/png', 'alt_text' => 'Esports Gaming Setup'],
            ['path' => 'media/cosplay_photo.jpg', 'original_filename' => 'cosplay_photo.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'alt_text' => 'Cosplay Championship Showcase'],
            ['path' => 'media/kpop_album.jpg', 'original_filename' => 'kpop_album.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'alt_text' => 'K-Pop Album Cover'],
            ['path' => 'media/trailer_video.mp4', 'original_filename' => 'trailer_video.mp4', 'media_type' => 'video', 'mime_type' => 'video/mp4', 'alt_text' => 'Official Trailer Video'],
            ['path' => 'media/theme_ost.mp3', 'original_filename' => 'theme_ost.mp3', 'media_type' => 'audio', 'mime_type' => 'audio/mpeg', 'alt_text' => 'Official Soundtrack OST'],
            ['path' => 'media/fan_guide.pdf', 'original_filename' => 'fan_guide.pdf', 'media_type' => 'document', 'mime_type' => 'application/pdf', 'alt_text' => 'Fan Hub Official Convention Guide'],
            ['path' => 'media/merch_preview.jpg', 'original_filename' => 'merch_preview.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'alt_text' => 'Limited Edition Merch Item'],
            ['path' => 'media/avatar_default.png', 'original_filename' => 'avatar_default.png', 'media_type' => 'image', 'mime_type' => 'image/png', 'alt_text' => 'Default User Profile Avatar'],
        ];

        foreach ($mediaItems as $item) {
            Media::updateOrCreate(
                ['path' => $item['path']],
                array_merge($item, [
                    'uploaded_by' => $admin?->id,
                    'disk' => 'public',
                    'size_bytes' => 102400,
                    'width' => 1920,
                    'height' => 1080,
                ])
            );
        }
    }
}
