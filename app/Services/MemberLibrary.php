<?php

namespace App\Services;

use App\Models\{ActivityLog, CharacterProfile, Content, Event, MerchandiseItem};
use Illuminate\Database\Eloquent\Model;

class MemberLibrary
{
    public const TYPES = ['content' => Content::class, 'character' => CharacterProfile::class, 'merchandise' => MerchandiseItem::class, 'event' => Event::class];

    public function resolve(string $type, int $id): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $query = self::TYPES[$type]::query();
        if ($type === 'content') $query->visibleToPublic();
        if ($type === 'event') $query->published();
        return $query->findOrFail($id);
    }

    public function card(?Model $item): ?array
    {
        if (! $item) return null;
        if ($item instanceof Content && ($item->status !== 'published' || $item->published_at?->isFuture())) return null;
        if ($item instanceof Event && $item->status !== 'published') return null;
        $type = array_search(get_class($item), self::TYPES, true);
        if (! $type) return null;
        $route = match ($type) {
            'content' => 'public.content', 'character' => 'public.character', 'merchandise' => 'public.merchandise',
            'event' => 'events.show',
        };
        return ['id' => $item->id, 'type' => $type, 'title' => $item->title ?? $item->name,
            'image' => $item->artwork_url ?: asset('images/fandoms/anime.png'),
            'category' => $item->category?->name ?? ucfirst($type), 'url' => route($route, $item->slug)];
    }

    public function activity(string $event, Model $target): void
    {
        if (! auth()->check()) return;
        ActivityLog::create(['user_id' => auth()->id(), 'event' => 'member.'.$event,
            'auditable_type' => $target->getMorphClass(), 'auditable_id' => $target->id,
            'new_values' => ['label' => $target->title ?? $target->name ?? 'Your account']]);
    }

    public function viewed(Model $target): void
    {
        if (! auth()->check()) return;
        $recent = ActivityLog::where('user_id', auth()->id())->where('event', 'member.viewed')
            ->where('auditable_type', $target->getMorphClass())->where('auditable_id', $target->id)
            ->where('created_at', '>=', now()->subMinutes(30))->exists();
        if (! $recent) $this->activity('viewed', $target);
    }
}