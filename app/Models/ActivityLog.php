<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    /**
     * Event key => [label, chip tone, icon].
     */
    public const EVENTS = [
        'created' => ['Created', 'success', 'bi-plus-circle'],
        'updated' => ['Updated', 'warning', 'bi-pencil'],
        'deleted' => ['Deleted', 'danger', 'bi-trash'],
        'restored' => ['Restored', 'info', 'bi-arrow-counterclockwise'],
        'force_deleted' => ['Permanently deleted', 'danger', 'bi-exclamation-octagon'],
        'login' => ['Signed in', 'accent', 'bi-box-arrow-in-right'],
    ];

    protected $fillable = [
        'user_id',
        'event',
        'subject',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Memoised diff, so `field_changes`, `field_change_count` and `summary`
     * share one build.
     *
     * Note: this cannot be named `changes` — Eloquent declares a real
     * `protected $changes` property on every model, which shadows the accessor
     * for any read performed from inside the class.
     *
     * @var array<int, array{field: string, label: string, old: mixed, new: mixed, type: string}>|null
     */
    private ?array $fieldChangesCache = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfEvent(Builder $query, ?string $event): Builder
    {
        return $event ? $query->where('event', $event) : $query;
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('auditable_type', $type) : $query;
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * Write an audit entry, snapshotting a readable subject and sentence.
     */
    public static function log(
        string $event,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
    ): static {
        $logger = app(ActivityLogger::class);

        $oldValues = $logger->redact($oldValues);
        $newValues = $logger->redact($newValues);

        $subject = $logger->subjectFor($model) ?? $logger->subjectFromValues($newValues ?: $oldValues);

        return static::create([
            'user_id' => $actor?->id ?? auth()->id(),
            'event' => $event,
            'subject' => $subject,
            'description' => $logger->describe($event, $model::class, $subject, $oldValues, $newValues),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
        ]);
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENTS[$this->event][0] ?? ucfirst(str_replace('_', ' ', (string) $this->event));
    }

    public function getEventToneAttribute(): string
    {
        return self::EVENTS[$this->event][1] ?? 'muted';
    }

    public function getEventIconAttribute(): string
    {
        return self::EVENTS[$this->event][2] ?? 'bi-dot';
    }

    public function getModelLabelAttribute(): string
    {
        return app(ActivityLogger::class)->modelLabel($this->auditable_type);
    }

    public function getActorNameAttribute(): string
    {
        return $this->user?->name ?? 'System';
    }

    /**
     * Fall back to a generated sentence for rows written before this column existed.
     */
    public function getDescriptionAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }

        return app(ActivityLogger::class)->describe(
            (string) $this->event,
            $this->auditable_type,
            $this->subject,
            $this->normalizeValues($this->old_values),
            $this->normalizeValues($this->new_values),
        );
    }

    /**
     * Coerce a stored value bag to an array.
     *
     * Legacy rows were written through a seeder that called json_encode() on an
     * already-cast value, so the column holds a JSON *string* rather than a JSON
     * object. Decoding the cast once yields a string, which must be unwrapped.
     */
    protected function normalizeValues(mixed $values): ?array
    {
        if ($values === null || $values === '') {
            return null;
        }

        if (is_string($values)) {
            $decoded = json_decode($values, true);
            $values = is_array($decoded) ? $decoded : ['value' => $values];
        }

        return is_array($values) ? $values : null;
    }

    /**
     * Field-by-field old -> new rows for the detail view.
     *
     * Memoised because the list view reads `changes`, `change_count` and
     * `summary` for every row, and each would otherwise rebuild the diff.
     *
     * @return array<int, array{field: string, label: string, old: mixed, new: mixed, type: string}>
     */
    public function getChangesAttribute(): array
    {
        return $this->changeCache ??= app(ActivityLogger::class)->diff(
            $this->event,
            $this->normalizeValues($this->old_values),
            $this->normalizeValues($this->new_values),
        );
    }

    public function getChangeCountAttribute(): int
    {
        return count(array_filter(
            $this->changes,
            fn (array $change) => $change['type'] !== 'unchanged',
        ));
    }

    /**
     * A compact "field: old -> new" preview for the list view.
     */
    public function getSummaryAttribute(): string
    {
        $logger = app(ActivityLogger::class);

        $parts = collect($this->changes)
            ->reject(fn (array $change) => $change['type'] === 'unchanged')
            ->take(3)
            ->map(function (array $change) use ($logger) {
                return match ($change['type']) {
                    'added' => $change['label'].' = '.$logger->formatValue($change['new'], 40),
                    'removed' => $change['label'].' removed',
                    default => $change['label'].': '.$logger->formatValue($change['old'], 30).' → '.$logger->formatValue($change['new'], 30),
                };
            })
            ->all();

        return implode(' · ', $parts);
    }

    /**
     * Whether the underlying record still exists, so the UI can offer a link.
     */
    public function getTargetExistsAttribute(): bool
    {
        return $this->auditable !== null;
    }

    /**
     * Admin URL for the underlying record, when one is known.
     */
    public function getTargetUrlAttribute(): ?string
    {
        $target = $this->auditable;

        if (! $target || ! $target->exists) {
            return null;
        }

        $key = $target->getKey();
        $slug = $target->slug ?? null;

        return match ($this->auditable_type) {
            Category::class => route('admin.categories.edit', $key),
            CharacterProfile::class => route('admin.characters.edit', $key),
            Content::class => route('admin.contents.edit', $slug ?: $key),
            Event::class => route('admin.events.edit', $key),
            MerchandiseItem::class => route('admin.merchandise.edit', $key),
            Role::class => route('admin.roles.edit', $key),
            Tag::class => route('admin.tags.edit', $key),
            User::class => route('admin.users.edit', $key),
            default => null,
        };
    }

    /**
     * Distinct actors for the "filter by user" dropdown, cached because the
     * list only changes when a new actor appears.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    public static function actors()
    {
        return Cache::remember('activity-log-actors', 300, fn () => static::query()
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->with('user:id,name')
            ->get()
            ->map(fn (self $log) => $log->user)
            ->filter()
            ->sortBy('name')
            ->values());
    }

    /**
     * Forget cached actor list after a new entry is written.
     */
    protected static function booted(): void
    {
        static::created(fn () => Cache::forget('activity-log-actors'));
    }
}
