<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\ContentMedia;
use App\Models\Media;
use Illuminate\Database\Seeder;

class ContentMediaSeeder extends Seeder
{
    public function run(): void
    {
        $contents = Content::all();
        $mediaList = Media::all();

        if ($contents->isEmpty() || $mediaList->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($contents as $content) {
            foreach ($mediaList as $media) {
                if ($count >= 10) {
                    break 2;
                }

                ContentMedia::updateOrCreate(
                    [
                        'content_id' => $content->id,
                        'media_id' => $media->id,
                    ],
                    [
                        'role' => ($count % 2 === 0) ? 'cover' : 'gallery',
                        'sort_order' => $count + 1,
                    ]
                );
                $count++;
            }
        }
    }
}
