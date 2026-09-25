<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Content;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Seed a believable audit trail using only the event types the application
     * actually writes. Each row goes through ActivityLogger so the generated
     * subject and description match what a real change would produce.
     */
    public function run(): void
    {
        $logger = app(ActivityLogger::class);

        $users = User::all();
        $contents = Content::all();
        $categories = Category::all();

        if ($users->isEmpty() || $contents->isEmpty()) {
            return;
        }

        $actors = $users->values();
        $rows = [];

        foreach ($contents->take(6) as $index => $content) {
            $actor = $actors[$index % $actors->count()];

            $rows[] = $this->entry('created', $content, $actor, null, $content->getAttributes(), $index);
            $rows[] = $this->entry('updated', $content, $actor, [
                'status' => 'draft',
                'release_label' => null,
            ], [
                'status' => 'published',
                'release_label' => 'Premiere',
            ], $index + 1);

            $rows[] = $this->entry('login', $actor, $actor, null, [
                'email' => $actor->email,
                'guard' => 'web',
            ], $index + 2);
        }

        foreach ($categories->take(4) as $index => $category) {
            $actor = $actors[$index % $actors->count()];

            $rows[] = $this->entry('updated', $category, $actor, [
                'name' => $category->name,
                'description' => $category->description,
            ], [
                'name' => $category->name,
                'description' => trim(($category->description ?? '').' Updated by the community team.'),
            ], $index + 8);
        }

        $rows = array_filter($rows);

        // Oldest first so `latest()` on the admin page reads newest-first.
        foreach (array_reverse($rows) as $offset => $row) {
            ActivityLog::create($row['data'] + ['created_at' => now()->subMinutes($offset * 37)]);
        }
    }

    /**
     * @return array{data: array<string, mixed>}|null
     */
    private function entry(string $event, $subject, User $actor, ?array $old, ?array $new, int $offset): ?array
    {
        $logger = app(ActivityLogger::class);

        $old = $logger->redact($old);
        $new = $logger->redact($new);

        $label = $logger->subjectFor($subject) ?? $logger->subjectFromValues($new ?: $old);

        if ($label === null) {
            return null;
        }

        return [
            'data' => [
                'user_id' => $actor->id,
                'event' => $event,
                'subject' => $label,
                'description' => $logger->describe($event, $subject::class, $label, $old, $new),
                'auditable_type' => $subject::class,
                'auditable_id' => $subject->getKey(),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            ],
        ];
    }
}
