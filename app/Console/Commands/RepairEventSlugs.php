<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class RepairEventSlugs extends Command
{
    protected $signature = 'events:repair-slugs';

    protected $description = 'Generate missing event slugs without changing existing URLs or event details';

    public function handle(): int
    {
        $repaired = 0;
        Event::whereNull('slug')->orWhereRaw("TRIM(slug) = ''")->chunkById(100, function ($events) use (&$repaired) {
            foreach ($events as $event) {
                $event->ensureSlug();
                Event::whereKey($event->id)->where(fn ($query) => $query->whereNull('slug')->orWhereRaw("TRIM(slug) = ''"))
                    ->update(['slug' => $event->slug]);
                $repaired++;
            }
        });
        $this->info("Repaired {$repaired} event slugs.");

        return self::SUCCESS;
    }
}
