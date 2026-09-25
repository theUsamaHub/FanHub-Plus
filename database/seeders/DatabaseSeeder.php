<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Roles & Core Auth
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);

        // 2. Base Categories, Media, Tags, Settings
        $this->call(CategorySeeder::class);
        $this->call(MediaSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(SettingsSeeder::class);

        // 3. User Relations
        $this->call(UserProfileSeeder::class);
        $this->call(UserFavoriteCategorySeeder::class);

        // 4. Main Domain Models (Characters, Content, Media Pivots, Merch, Events)
        $this->call(CharacterProfileSeeder::class);
        $this->call(ContentSeeder::class);
        $this->call(ContentMediaSeeder::class);
        $this->call(MerchandiseItemSeeder::class);
        $this->call(EventSeeder::class);
        $this->call(UpcomingReleaseSeeder::class);

        // 5. User Interaction Models (Bookmarks, Ratings, Reviews, Feedback)
        $this->call(BookmarkSeeder::class);
        $this->call(RatingSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(FeedbackSeeder::class);

        // 6. Support & Communication Models (Contacts, Subscribers, Chatbot, Auditing)
        $this->call(ContactSeeder::class);
        $this->call(SubscriberSeeder::class);
        $this->call(ChatbotFaqSeeder::class);
        $this->call(ChatbotQuerySeeder::class);
        $this->call(ActivityLogSeeder::class);
    }
}
