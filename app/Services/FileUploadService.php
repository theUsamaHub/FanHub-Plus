<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FileUploadService
{
    public const FILE_TYPES = [
        'images' => [
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'max_size' => 5120,
        ],
        'documents' => [
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ],
            'extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'max_size' => 25600,
        ],
        'spreadsheets' => [
            'mime_types' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
            ],
            'extensions' => ['xls', 'xlsx', 'csv'],
            'max_size' => 25600,
        ],
        'videos' => [
            'mime_types' => [
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'video/x-ms-wmv',
                'video/3gpp',
            ],
            'extensions' => ['mp4', 'webm', 'ogv', 'mov', 'avi', 'mkv', 'wmv', '3gp'],
            'max_size' => 102400,
        ],
        'movies' => [
            'mime_types' => [
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'video/x-ms-wmv',
                'video/3gpp',
            ],
            'extensions' => ['mp4', 'webm', 'ogv', 'mov', 'avi', 'mkv', 'wmv', '3gp'],
            'max_size' => 524288,
        ],
        'audio' => [
            'mime_types' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/aac'],
            'extensions' => ['mp3', 'wav', 'ogg', 'm4a', 'aac'],
            'max_size' => 20480,
        ],
    ];

    public function upload(
        UploadedFile $file,
        string $directory = 'uploads',
        ?string $disk = null,
        ?int $uploadedBy = null,
        ?string $altText = null,
        ?float $duration = null,
        ?int $categoryId = null
    ): Media {
        $disk = $disk ?? config('filesystems.media_disk', 'public');
        $path = $file->store($directory, $disk);

        if (! is_string($path) || $path === '' || $path === '0') {
            throw new RuntimeException("Failed to store uploaded file [{$file->getClientOriginalName()}] on disk [{$disk}].");
        }

        $mimeType = $file->getMimeType();

        $data = [
            'uploaded_by' => $uploadedBy ?? auth()->id(),
            'category_id' => $categoryId,
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

    public function delete(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);

        return $media->delete();
    }

    public function updateMetadata(Media $media, array $data): Media
    {
        $media->fill([
            'alt_text' => $data['alt_text'] ?? null,
            'duration' => $data['duration'] ?? null,
            'category_id' => $data['category_id'] ?? null,
        ])->save();

        return $media->fresh();
    }

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

    public static function getAllowedExtensions(): array
    {
        $extensions = [];
        foreach (self::FILE_TYPES as $category) {
            $extensions = array_merge($extensions, $category['extensions']);
        }

        return array_unique($extensions);
    }

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
