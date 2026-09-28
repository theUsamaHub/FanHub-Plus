<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // (slug, type, status, message) — the slug is used as a stable key so
        // the seeder stays idempotent across `migrate:fresh --seed` reruns.
        $feedbackItems = [
            ['dark-mode-landing', 'suggestion', 'resolved', 'Please add a dark mode toggle directly in the main landing page header so visitors can switch without going through settings.'],
            ['mobile-video-controls', 'bug', 'open', 'The video player controls overlap with the bottom navigation on mobile screens below 380px wide.'],
            ['verified-creator-badges', 'query', 'in_review', 'How can community creators apply for verified creator badges once they cross the eligibility threshold?'],
            ['custom-soundtrack-playlists', 'suggestion', 'open', 'Allow members to build and share custom soundtrack playlists from the audio clip library.'],
            ['profile-upload-500', 'bug', 'resolved', 'Profile picture upload throws a 500 error whenever the file is over the 2MB limit; please surface a friendly validation message instead.'],
            ['event-location-filter', 'suggestion', 'in_review', 'Add a filter by event location (city / venue) on the global events calendar so fans can narrow down nearby cons.'],
            ['merch-tracking-link', 'query', 'closed', 'Where can I find the official tracking link once my order ships from the merchandise store?'],
            ['monthly-fanart-contest', 'suggestion', 'open', 'Run monthly community fan-art voting contests with featured winners promoted on the homepage.'],
            ['bookmark-state-stale', 'bug', 'resolved', 'The bookmark icon state does not refresh after toggling until the page is reloaded.'],
            ['preorder-notifications', 'suggestion', 'in_review', 'Send notification reminders before limited merchandise pre-orders go live so fans do not miss them.'],
            ['event-rsvp-feature', 'suggestion', 'open', 'Let members RSVP to nearby events from the event card and surface a personal "My Events" list.'],
            ['search-relevance', 'bug', 'open', 'Global search returns fan submissions even when filtering for editorial stories only.'],
            ['saved-search', 'suggestion', 'in_review', 'Allow saving advanced search queries so frequent filters can be reused with one click.'],
            ['creator-payouts', 'query', 'open', 'When will the creator payout dashboard be available to track earnings from merchandise referrals?'],
            ['regional-pricing', 'suggestion', 'in_review', 'Show regional pricing on merchandise pages for fans outside the US market.'],
            ['offline-reading', 'suggestion', 'open', 'Add an offline reading mode for editorial stories so members can save them as PDF / ePub for travel.'],
            ['event-recap-template', 'query', 'closed', 'Is there an official recap template editors can use to write post-event coverage stories?'],
            ['spam-reports', 'bug', 'resolved', 'Spam accounts are slipping past registration; please tighten bot checks on the signup endpoint.'],
            ['language-filter', 'suggestion', 'in_review', 'Tag content with audio language so we can build a "dubbed vs subbed" filter for anime episodes.'],
            ['mobile-nav-drawer', 'bug', 'open', 'The mobile navigation drawer flickers when the browser address bar collapses on scroll.'],
        ];

        // The Feedback model only accepts the canonical type set, so filter any
        // experimental value back to a safe default. Keeps the seeder safe to run.
        $allowedTypes = ['bug', 'suggestion', 'query'];
        $allowedStatuses = ['open', 'in_review', 'resolved', 'closed'];

        foreach ($feedbackItems as $index => $item) {
            $user = $users->isEmpty() ? null : $users[$index % $users->count()];

            Feedback::updateOrCreate(
                ['message' => $item[3]],
                [
                    'user_id' => $user?->id,
                    'type' => in_array($item[1], $allowedTypes, true) ? $item[1] : 'query',
                    'status' => in_array($item[2], $allowedStatuses, true) ? $item[2] : 'open',
                    'message' => $item[3],
                    'created_at' => now()->subDays(20 - min($index, 19)),
                    'updated_at' => now()->subDays(max(0, 15 - min($index, 15))),
                ],
            );
        }
    }
}