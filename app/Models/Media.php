<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'uploaded_by',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'media_type',
        'size_bytes',
        'width',
        'height',
        'duration',
        'alt_text',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'decimal:2',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_media')
            ->withPivot(['role', 'sort_order']);
    }

    public function getUrlAttribute(): string
    {
        if (! $this->hasValidPath()) {
            return '';
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function hasValidPath(): bool
    {
        return is_string($this->path) && $this->path !== '' && $this->path !== '0';
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size_bytes ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function isAudio(): bool
    {
        return $this->media_type === 'audio';
    }

    public function isDocument(): bool
    {
        return $this->media_type === 'document';
    }

    /**
     * Split stored duration (seconds) into hours, minutes, and seconds parts.
     *
     * @return array{hours: int, minutes: int, seconds: float}
     */
    public function getDurationPartsAttribute(): array
    {
        $total = (float) ($this->duration ?? 0);

        return [
            'hours' => (int) floor($total / 3600),
            'minutes' => (int) floor(fmod($total, 3600) / 60),
            'seconds' => round(fmod($total, 60), 2),
        ];
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $parts = $this->duration_parts;
        $seconds = $parts['seconds'] == (int) $parts['seconds']
            ? (string) (int) $parts['seconds']
            : (string) $parts['seconds'];

        return sprintf('%d:%02d:%s', $parts['hours'], $parts['minutes'], str_pad($seconds, 2, '0', STR_PAD_LEFT));
    }

    /**
     * Count entities that still point at this media record.
     */
    public function referenceCount(): int
    {
        return Category::where('icon_media_id', $this->id)->count()
            + CharacterProfile::where('image_media_id', $this->id)->count()
            + MerchandiseItem::where('image_media_id', $this->id)->count()
            + Event::where('cover_media_id', $this->id)->count()
            + \Illuminate\Support\Facades\DB::table('event_media')->where('media_id', $this->id)->count()
            + UserProfile::where('avatar_media_id', $this->id)->count()
            + ContentMedia::where('media_id', $this->id)->count();
    }

    public function isReferenced(): bool
    {
        return $this->referenceCount() > 0;
    }
}
