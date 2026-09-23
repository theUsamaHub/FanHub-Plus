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
        return Storage::disk($this->disk)->url($this->path);
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
     * Count entities that still point at this media record.
     */
    public function referenceCount(): int
    {
        return Category::where('icon_media_id', $this->id)->count()
            + CharacterProfile::where('image_media_id', $this->id)->count()
            + MerchandiseItem::where('image_media_id', $this->id)->count()
            + Event::where('cover_media_id', $this->id)->count()
            + UserProfile::where('avatar_media_id', $this->id)->count()
            + ContentMedia::where('media_id', $this->id)->count();
    }

    public function isReferenced(): bool
    {
        return $this->referenceCount() > 0;
    }
}
