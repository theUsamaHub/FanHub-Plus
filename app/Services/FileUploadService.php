<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Allowed file types organized by category.
     */
    public const FILE_TYPES = [
        'images' => [
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'max_size' => 5120, // 5MB
        ],
        'documents' => [
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ],
            'extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'max_size' => 10240, // 10MB
        ],
        'spreadsheets' => [
            'mime_types' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
            ],
            'extensions' => ['xls', 'xlsx', 'csv'],
            'max_size' => 10240, // 10MB
        ],
        'videos' => [
            'mime_types' => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
            'extensions' => ['mp4', 'webm', 'ogv', 'mov'],
            'max_size' => 51200, // 50MB
        ],
        'audio' => [
            'mime_types' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/aac'],
            'extensions' => ['mp3', 'wav', 'ogg', 'm4a', 'aac'],
            'max_size' => 20480, // 20MB
        ],
    ];

    /**
     * Upload a file and create a media record.
     */
    public function upload(
        UploadedFile $file,
        string $directory = 'uploads',
        ?string $disk = null,
        ?int $uploadedBy = null,
        ?string $altText = null,
        ?float $duration = null
    ): Media {
        $disk = $disk ?? config('filesystems.media_disk', 'public');
        $path = $file->store($directory, $disk);
        $mimeType = $file->getMimeType();

        $data = [
            'uploaded_by' => $uploadedBy ?? auth()->id(),
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'media_type' => self::mediaTypeFromMime($mimeType),
            'size_bytes' => $file->getSize(),
            'disk' => $disk,
            'alt_text' => $altText,
            'duration' => $duration,
        ];

        if ($data['media_type'] === 'image') {
            $info = @getimagesize($file->getRealPath());
            if ($info !== false) {
                $data['width'] = $info[0];
                $data['height'] = $info[1];
            }
        }

        return Media::create($data);
    }

    /**
     * Upload multiple files.
     */
    public function uploadMultiple(
        array $files,
        string $directory = 'uploads',
        ?string $disk = null
    ): array {
        $media = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $media[] = $this->upload($file, $directory, $disk);
            }
        }

        return $media;
    }

    /**
     * Delete a media file and its record.
     */
    public function delete(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);

        return $media->delete();
    }

    /**
     * Update media metadata (alt text, duration).
     */
    public function updateMetadata(Media $media, array $data): Media
    {
        $media->fill([
            'alt_text' => $data['alt_text'] ?? null,
            'duration' => $data['duration'] ?? null,
        ])->save();

        return $media->fresh();
    }

    /**
     * File-type category for a given MIME type (images|documents|spreadsheets|videos|audio).
     */
    public static function categoryFromMime(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'images',
            str_starts_with($mimeType, 'video/') => 'videos',
            str_starts_with($mimeType, 'audio/') => 'audio',
            in_array($mimeType, self::FILE_TYPES['spreadsheets']['mime_types'], true) => 'spreadsheets',
            default => 'documents',
        };
    }

    /**
     * Map a MIME type to the media_type enum value.
     */
    public static function mediaTypeFromMime(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        return 'document';
    }

    /**
     * Get validation rules for a file type category.
     */
    public static function getValidationRules(string $category = 'images'): array
    {
        $type = self::FILE_TYPES[$category] ?? self::FILE_TYPES['images'];

        return [
            'required',
            'file',
            'mimes:'.implode(',', $type['extensions']),
            'max:'.$type['max_size'],
        ];
    }

    /**
     * Get all allowed extensions.
     */
    public static function getAllowedExtensions(): array
    {
        $extensions = [];
        foreach (self::FILE_TYPES as $category) {
            $extensions = array_merge($extensions, $category['extensions']);
        }

        return array_unique($extensions);
    }

    /**
     * Get file type info from mime type.
     */
    public static function getFileTypeInfo(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        return match ($mimeType) {
            'application/pdf' => 'pdf',
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'spreadsheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
            default => 'file',
        };
    }
}
