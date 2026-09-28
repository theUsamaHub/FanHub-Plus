<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(CategorySeeder::class);
        $this->call(MediaSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(SettingsSeeder::class);

        $this->call(UserProfileSeeder::class);
        $this->call(UserFavoriteCategorySeeder::class);

        $this->call(CharacterProfileSeeder::class);
        $this->call(ContentSeeder::class);
        $this->call(ContentMediaSeeder::class);
        $this->call(MerchandiseItemSeeder::class);
        $this->call(EventSeeder::class);

        $this->call(BookmarkSeeder::class);
        $this->call(RatingSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(FeedbackSeeder::class);

        $this->call(ContactSeeder::class);
        $this->call(SubscriberSeeder::class);
        $this->call(ChatbotFaqSeeder::class);
        $this->call(ChatbotQuerySeeder::class);
        $this->call(ActivityLogSeeder::class);
    }
}
