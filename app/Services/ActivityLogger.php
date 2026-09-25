<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\MerchandiseItem;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Translates raw model attribute dumps into human-readable audit trail entries.
 *
 * Everything the admin activity log renders as prose originates here, so the
 * wording stays consistent between the list, the detail page and the CSV export.
 */
class ActivityLogger
{
    /**
     * Friendly singular name for each auditable model.
     */
    public const MODEL_LABELS = [
        Category::class => 'Fandom',
        CharacterProfile::class => 'Character',
        Content::class => 'Story',
        Event::class => 'Event',
        Media::class => 'Media',
        MerchandiseItem::class => 'Merchandise',
        Role::class => 'Role',
        Setting::class => 'Setting',
        Tag::class => 'Tag',
        User::class => 'User',
    ];

    /**
     * Attribute that best identifies a record of the given type.
     */
    public const SUBJECT_ATTRIBUTES = [
        Category::class => 'name',
        CharacterProfile::class => 'name',
        Content::class => 'title',
        Event::class => 'title',
        Media::class => 'original_filename',
        MerchandiseItem::class => 'name',
        Role::class => 'name',
        Setting::class => 'key',
        Tag::class => 'name',
        User::class => 'name',
    ];

    /**
     * Never persisted into the log, even when the attribute is dirty.
     */
    public const HIDDEN_ATTRIBUTES = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'secret',
        'token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Bookkeeping attributes that change constantly and carry no audit value.
     */
    public const NOISE_ATTRIBUTES = [
        'created_at',
        'updated_at',
        'deleted_at',
        'view_count',
        'popularity_score',
        'remember_token',
    ];

    /**
     * Field names that do not read well when naively title-cased.
     */
    public const FIELD_LABELS = [
        'category_id' => 'Fandom',
        'icon_media_id' => 'Icon',
        'image_media_id' => 'Image',
        'cover_media_id' => 'Cover image',
        'avatar_media_id' => 'Avatar',
        'uploaded_by' => 'Uploaded by',
        'submitted_by' => 'Submitted by',
        'reviewed_by' => 'Reviewed by',
        'is_active' => 'Active',
        'is_featured' => 'Featured',
        'is_published' => 'Published',
        'is_upcoming' => 'Upcoming',
        'is_user_submitted' => 'User submitted',
        'email_verified_at' => 'Email verified',
        'published_at' => 'Published at',
        'release_date' => 'Release date',
        'release_label' => 'Release label',
        'start_at' => 'Starts at',
        'end_at' => 'Ends at',
        'published' => 'Published',
        'slug' => 'Slug',
        'url' => 'URL',
        'ip_address' => 'IP address',
        'user_agent' => 'Browser',
    ];

    /**
     * Phrases used when building the log description.
     */
    public const EVENT_PHRASES = [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
        'restored' => 'restored',
        'force_deleted' => 'permanently deleted',
        'login' => 'signed in',
    ];

    public function modelLabel(?string $type): string
    {
        if (! $type) {
            return 'Record';
        }

        return self::MODEL_LABELS[$type] ?? class_basename($type);
    }

    /**
     * The human label for a record, snapshotted so the entry stays readable
     * even after the record is renamed or deleted.
     */
    public function subjectFor(Model $model): ?string
    {
        $attribute = self::SUBJECT_ATTRIBUTES[$model::class] ?? 'name';

        $value = $model->getAttribute($attribute)
            ?? $model->getAttribute('title')
            ?? $model->getAttribute('name');

        if (! is_string($value) || trim($value) === '') {
            $value = $model->getAttribute('id') !== null
                ? '#'.$model->getKey()
                : null;
        }

        return $value === null ? null : Str::limit(trim((string) $value), 200, '');
    }

    /**
     * Rebuild a subject from a stored value bag (used when backfilling).
     */
    public function subjectFromValues(?array $values): ?string
    {
        if (! $values) {
            return null;
        }

        $value = collect(['title', 'name', 'key', 'original_filename'])
            ->map(fn (string $key) => $values[$key] ?? null)
            ->filter(fn ($candidate) => is_string($candidate) && trim($candidate) !== '')
            ->first();

        return $value === null ? null : Str::limit(trim($value), 200, '');
    }

    /**
     * Dirty attributes worth auditing: secrets and counters removed.
     *
     * @return array<string, mixed>
     */
    public function changed(Model $model): array
    {
        return $this->redact($model->getDirty());
    }

    /**
     * Strip secrets and bookkeeping noise from an attribute bag.
     */
    public function redact(?array $values): array
    {
        if (! $values) {
            return [];
        }

        return array_diff_key($values, array_flip(self::HIDDEN_ATTRIBUTES), array_flip(self::NOISE_ATTRIBUTES));
    }

    /**
     * Build the one-line sentence shown in the log list.
     */
    public function describe(string $event, ?string $modelType, ?string $subject, ?array $old, ?array $new): string
    {
        $label = $this->modelLabel($modelType);
        $phrase = self::EVENT_PHRASES[$event] ?? $event;
        $target = $subject ? '"'.$subject.'"' : '';

        if ($event === 'created') {
            return $target
                ? "{$label} {$target} was created"
                : "A new {$label} was created";
        }

        if ($event === 'updated') {
            $count = count($this->redact($new));

            if ($count === 1) {
                $detail = $this->fieldLabel(array_key_first($this->redact($new)));

                return $target
                    ? "{$label} {$target} — {$detail} changed"
                    : "{$label} — {$detail} changed";
            }

            if ($count > 1) {
                return $target
                    ? "{$label} {$target} — {$count} fields changed"
                    : "{$label} — {$count} fields changed";
            }

            return $target
                ? "{$label} {$target} was updated"
                : "{$label} was updated";
        }

        if ($event === 'deleted') {
            return $target
                ? "{$label} {$target} was deleted"
                : "{$label} was deleted";
        }

        if ($event === 'restored') {
            return $target
                ? "{$label} {$target} was restored"
                : "{$label} was restored";
        }

        if ($event === 'login') {
            return $target
                ? "{$label} {$target} signed in"
                : "{$label} signed in";
        }

        return Str::ucfirst("{$phrase} {$label}").($target ? " {$target}" : '');
    }

    /**
     * Turn a stored event into rows suitable for an old -> new diff table.
     *
     * @return array<int, array{field: string, label: string, old: mixed, new: mixed, type: string}>
     */
    public function diff(string $event, ?array $old, ?array $new): array
    {
        $old = $this->redact($old);
        $new = $this->redact($new);

        $keys = array_values(array_unique([...array_keys($old), ...array_keys($new)]));
        $rows = [];

        foreach ($keys as $key) {
            $hasOld = array_key_exists($key, $old);
            $hasNew = array_key_exists($key, $new);
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;

            if ($event === 'created' && $hasNew) {
                $type = 'added';
            } elseif ($event === 'deleted' && $hasOld) {
                $type = 'removed';
            } elseif ($hasOld && $hasNew && $before !== $after) {
                $type = 'changed';
            } elseif ($hasOld && ! $hasNew) {
                $type = 'removed';
            } elseif (! $hasOld && $hasNew) {
                $type = 'added';
            } else {
                $type = 'unchanged';
            }

            $rows[] = [
                'field' => $key,
                'label' => $this->fieldLabel($key),
                'old' => $before,
                'new' => $after,
                'type' => $type,
            ];
        }

        return $rows;
    }

    /**
     * Human label for a raw attribute name.
     */
    public function fieldLabel(string $key): string
    {
        if (isset(self::FIELD_LABELS[$key])) {
            return self::FIELD_LABELS[$key];
        }

        $key = preg_replace('/_id$/', '', $key) ?? $key;

        return Str::headline($key);
    }

    /**
     * Render a stored value for display.
     */
    public function formatValue(mixed $value, int $limit = 120): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('M j, Y g:i A');
        }

        if (is_array($value)) {
            if ($value === []) {
                return '—';
            }

            return Str::limit(
                (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $limit,
                '…',
            );
        }

        if (is_object($value)) {
            return Str::limit(method_exists($value, '__toString') ? (string) $value : (string) json_encode($value), $limit, '…');
        }

        if (is_scalar($value)) {
            $value = (string) $value;

            if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $value) === 1) {
                try {
                    return Carbon::parse($value)->format('M j, Y g:i A');
                } catch (\Throwable) {
                    return Str::limit($value, $limit, '…');
                }
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                try {
                    return Carbon::parse($value)->format('M j, Y');
                } catch (\Throwable) {
                    return Str::limit($value, $limit, '…');
                }
            }

            return Str::limit($value, $limit, '…');
        }

        return Str::limit((string) json_encode($value), $limit, '…');
    }

    /**
     * Every model type that currently writes to the log, for the filter dropdown.
     *
     * Session events reuse the User type, since that is who signed in.
     *
     * @return array<string, string>
     */
    public function auditableTypes(): array
    {
        return self::MODEL_LABELS;
    }
}
