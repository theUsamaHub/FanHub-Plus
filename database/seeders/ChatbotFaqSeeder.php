<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChatbotFaq;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();
        $categories = Category::all();

        $faqs = [
            ['question' => 'What is FanHub-Plus?', 'answer' => 'FanHub-Plus is the ultimate fan community platform for anime, gaming, movies, K-Pop, and pop culture content.'],
            ['question' => 'How can I submit my fan stories or cosplay photos?', 'answer' => 'Navigate to the Community section and click "Submit Story" or "Upload Media" to submit your content for review.'],
            ['question' => 'Where can I buy official merchandise?', 'answer' => 'Visit the Merchandise tab to browse verified fan store collectibles, apparel, and limited-edition statues.'],
            ['question' => 'How do event ticket pre-orders work?', 'answer' => 'Event listings contain official external ticket partner links for direct purchasing and RSVP notifications.'],
            ['question' => 'Can I bookmark articles and events for later?', 'answer' => 'Yes! Click the Bookmark icon on any content or event card to save it to your personal profile.'],
            ['question' => 'How is the content popularity score calculated?', 'answer' => 'Popularity score is dynamically updated based on community views, bookmarks, ratings, and social shares.'],
            ['question' => 'What are Fan Hub badges and roles?', 'answer' => 'Active community members earn custom roles and badges (VIP, Creator, Reviewer) based on contributions.'],
            ['question' => 'How do I customize my notification preferences?', 'answer' => 'Go to Profile Settings > Notifications to customize email and in-app alerts for upcoming releases.'],
            ['question' => 'Is FanHub-Plus mobile friendly?', 'answer' => 'Yes! FanHub-Plus is fully responsive across desktop, tablet, and mobile web browsers.'],
            ['question' => 'How can I report inappropriate comments or content?', 'answer' => 'Click the flag/report button on any comment or submission to notify our moderation team immediately.'],
        ];

        foreach ($faqs as $index => $faq) {
            $category = $categories[$index % count($categories)] ?? null;

            ChatbotFaq::create([
                'category_id' => $category?->id,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'created_by' => $admin?->id,
            ]);
        }
    }
}
