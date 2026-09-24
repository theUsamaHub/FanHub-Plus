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
            ['name' => 'Action', 'color' => '#dc3545'],
            ['name' => 'Romance', 'color' => '#e83e8c'],
            ['name' => 'Sci-Fi', 'color' => '#0d6efd'],
            ['name' => 'Fantasy', 'color' => '#6f42c1'],
            ['name' => 'Comedy', 'color' => '#ffc107'],
            ['name' => 'Drama', 'color' => '#fd7e14'],
            ['name' => 'Horror', 'color' => '#212529'],
            ['name' => 'Thriller', 'color' => '#198754'],
            ['name' => 'Adventure', 'color' => '#20c997'],
            ['name' => 'Mystery', 'color' => '#0dcaf0'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($tag['name'])],
                [
                    'name' => $tag['name'],
                    'slug' => Str::slug($tag['name']),
                    'color' => $tag['color'],
                ]
            );
        }
    }
}
