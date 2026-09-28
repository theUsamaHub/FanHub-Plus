<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\MerchandiseItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MerchandiseItemSeeder extends Seeder
{
    public function run(): void
    {
        // Categories keyed by fandom so each merch item is tied to a real world.
        $categories = Category::whereIn('slug', [
            'anime', 'gaming', 'movies', 'tv-shows', 'k-pop', 'comics',
            'manga', 'cosplay', 'music', 'esports',
        ])->get()->keyBy('slug');

        // If CategorySeeder has not run yet, bail with a friendly hint so the
        // operator can fix the order without staring at a long SQL trace.
        if ($categories->isEmpty()) {
            $this->command?->warn('MerchandiseItemSeeder skipped: no categories found. Run CategorySeeder first.');
            return;
        }

        $items = [
            ['name' => 'Collectible Hero Figure — Anniversary Edition', 'tag' => 'collectible', 'category' => 'anime', 'is_upcoming' => true, 'release_date' => now()->addMonths(2)->toDateString(), 'description' => 'Hand-painted PVC figure with interchangeable hands and a brushed-metal display base. Limited anniversary release.'],
            ['name' => 'Neon City Oversized Tee', 'tag' => 'limited_edition', 'category' => 'manga', 'is_upcoming' => true, 'release_date' => now()->addWeeks(6)->toDateString(), 'description' => 'Heavyweight 240gsm cotton tee with neon city glow print. Numbered limited edition of 500.'],
            ['name' => 'Battle Pass Character Pin Set', 'tag' => 'standard', 'category' => 'gaming', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Set of six hard-enamel character pins from the latest battle pass. Pairs with the collector binder sleeve.'],
            ['name' => 'Movie Marathon Poster Bundle', 'tag' => 'standard', 'category' => 'movies', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Trio of retro-printed A2 posters celebrating cult classics. Ships rolled in a kraft tube.'],
            ['name' => 'TV Show Standee — Cliffhanger Edition', 'tag' => 'pre_order', 'category' => 'tv-shows', 'is_upcoming' => true, 'release_date' => now()->addMonths(1)->toDateString(), 'description' => 'Pre-order for the show finale standee. Ships after the finale episode airs.'],
            ['name' => 'Concert Lightstick Reissue', 'tag' => 'collectible', 'category' => 'k-pop', 'is_upcoming' => true, 'release_date' => now()->addWeeks(8)->toDateString(), 'description' => 'Bluetooth-sync concert lightstick with seven signature color presets and a glow-hilt umbrella.'],
            ['name' => 'Indie Comic Sketchbook Bundle', 'tag' => 'standard', 'category' => 'comics', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Bundle of three artist sketchbooks featuring variant cover artwork from indie creators.'],
            ['name' => 'Cosplay Armory Toolkit', 'tag' => 'standard', 'category' => 'cosplay', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Workshop-grade tool kit including heat pen, EVA foam cutter and snap-blade knife with safety guard.'],
            ['name' => 'Anime Original Soundtrack Vinyl', 'tag' => 'collectible', 'category' => 'music', 'is_upcoming' => true, 'release_date' => now()->addMonths(3)->toDateString(), 'description' => '180g heavyweight vinyl pressing of the iconic anime OST, includes 12-page lyric booklet and download code.'],
            ['name' => 'Esports Pro Jersey — Championship Run', 'tag' => 'limited_edition', 'category' => 'esports', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Sublimated pro jersey commemorating the championship run. Embroidered logo and stitched numbers.'],
            ['name' => 'Hero Pose 1/7 Scale Statue', 'tag' => 'pre_order', 'category' => 'anime', 'is_upcoming' => true, 'release_date' => now()->addMonths(5)->toDateString(), 'description' => '1/7 scale statue featuring hero pose with translucent energy effect piece. Hand-numbered certificate.'],
            ['name' => 'Manga Creator Sketchbox', 'tag' => 'standard', 'category' => 'manga', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Curated sketchbox featuring screen tones, G-pens, manga paper pads and creator tutorials.'],
            ['name' => 'Gaming Soundbar with Reactive Lighting', 'tag' => 'standard', 'category' => 'gaming', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Compact gaming soundbar with HDMI passthrough and reactive RGB side lighting synced to gameplay.'],
            ['name' => 'Limited SteelBook — Anniversary Reprint', 'tag' => 'limited_edition', 'category' => 'movies', 'is_upcoming' => true, 'release_date' => now()->addMonths(4)->toDateString(), 'description' => 'Embossed steelbook anniversary reprint with alternate ending bonus disc and art inserts.'],
            ['name' => 'Show Vault Vinyl Bundle', 'tag' => 'collectible', 'category' => 'tv-shows', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Vaulted vinyl figure bundle featuring three exclusive chase variants from the cult favourite show.'],
            ['name' => 'Idol Photobook — Encore Edition', 'tag' => 'collectible', 'category' => 'k-pop', 'is_upcoming' => true, 'release_date' => now()->addWeeks(10)->toDateString(), 'description' => 'Encore edition photobook with behind-the-scenes polaroids, signed transparency page and unboxing kit.'],
            ['name' => 'Variant Cover Slipcase Set', 'tag' => 'standard', 'category' => 'comics', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Five hard slipcases themed around variant cover artist signatures. Acid-free archival board.'],
            ['name' => 'Cosplay Wig Styling Workshop', 'tag' => 'pre_order', 'category' => 'cosplay', 'is_upcoming' => true, 'release_date' => now()->addWeeks(4)->toDateString(), 'description' => 'Live online wig styling workshop with downloadable workbook and one-on-one feedback session.'],
            ['name' => 'Concert Stage Pass Replica', 'tag' => 'collectible', 'category' => 'music', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Replica stage pass with rotating lanyard clip, glow back-print and serial-numbered ID strip.'],
            ['name' => 'Pro Jersey Signed Print', 'tag' => 'limited_edition', 'category' => 'esports', 'is_upcoming' => true, 'release_date' => now()->addWeeks(3)->toDateString(), 'description' => 'Signed 16x20 print of the championship roster, certificate of authenticity included.'],
            ['name' => 'Studio Headphones — Collector Series', 'tag' => 'standard', 'category' => 'gaming', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Wired studio headphones tuned for esports with detachable boom mic and interchangeable faceplates.'],
            ['name' => 'Manga Library Storage Box', 'tag' => 'standard', 'category' => 'manga', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Sturdy corrugated storage box with manga-safe lining and an integrated bookend divider.'],
            ['name' => 'Convention Cosplay Repair Kit', 'tag' => 'standard', 'category' => 'cosplay', 'is_upcoming' => false, 'release_date' => null, 'description' => 'Pocket-friendly repair kit with hot glue pen, thread spool and emergency snap fasteners.'],
        ];

        // content_id is NOT NULL on merchandise_items; ensure we always have
        // something to attach. Look for category-matching content first, then
        // any published content, and bail out of the loop when neither exists.
        $contentsByCategory = Content::query()
            ->where('status', 'published')
            ->get()
            ->groupBy('category_id');
        $fallbackContent = Content::query()->where('status', 'published')->first();

        if (! $fallbackContent) {
            $this->command?->warn('MerchandiseItemSeeder skipped: no published content found. Run ContentSeeder first.');
            return;
        }

        $characters = CharacterProfile::all()->groupBy('category_id');

        foreach ($items as $row) {
            $name = trim($row['name']);

            $category = $categories->get($row['category']) ?? $categories->first();
            $content = ($contentsByCategory[$category->id] ?? collect())->first() ?? $fallbackContent;
            $character = ($characters[$category->id] ?? collect())->first();

            MerchandiseItem::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $category->id,
                    'content_id' => $content->id,
                    'character_id' => $character?->id,
                    'name' => $name,
                    'description' => $row['description'] ?? null,
                    // Per task: skip media selection — the admin will attach images manually.
                    'image_media_id' => null,
                    'tag' => $row['tag'] ?? 'standard',
                    'is_upcoming' => (bool) ($row['is_upcoming'] ?? false),
                    'release_date' => $row['release_date'] ?? null,
                ],
            );
        }
    }
}