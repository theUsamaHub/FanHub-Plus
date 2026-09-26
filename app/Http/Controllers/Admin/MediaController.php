<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileService
    ) {}

    public function index(Request $request): View
    {
        $query = Media::with('uploadedBy');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('media_type', $type);
        }

        $media = $query->latest()->paginate(20)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:524288'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:23'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0', 'max:59.99'],
        ]);

        foreach ($request->file('files') as $file) {
            $category = FileUploadService::categoryFromMime($file->getMimeType() ?? '');
            $type = FileUploadService::FILE_TYPES[$category];
            $extension = strtolower($file->getClientOriginalExtension());

            if (! in_array($extension, $type['extensions'], true)) {
                return back()
                    ->withInput()
                    ->withErrors(['files' => "File type .{$extension} is not allowed."]);
            }

            if ($file->getSize() > $type['max_size'] * 1024) {
                return back()
                    ->withInput()
                    ->withErrors(['files' => "File \"{$file->getClientOriginalName()}\" exceeds the limit for {$category}."]);
            }
        }

        $duration = $this->durationFromParts($request);

        foreach ($request->file('files') as $file) {
            try {
                $this->fileService->upload(
                    $file,
                    'uploads/'.FileUploadService::categoryFromMime($file->getMimeType() ?? ''),
                    null,
                    null,
                    $request->input('alt_text'),
                    $duration
                );
            } catch (\RuntimeException $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['files' => __('Failed to upload ":name". Please try again.', ['name' => $file->getClientOriginalName()])]);
            }
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    /**
     * Convert hours + minutes + seconds inputs into total seconds.
     */
    private function durationFromParts(Request $request): ?float
    {
        $hours = (int) $request->input('duration_hours', 0);
        $minutes = (int) $request->input('duration_minutes', 0);
        $seconds = (float) $request->input('duration_seconds', 0);

        $total = ($hours * 3600) + ($minutes * 60) + $seconds;

        return $total > 0 ? round($total, 2) : null;
    }

    /**
     * Initialize a chunked upload session for large files.
     */
    public function initChunkedUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'total_size' => ['required', 'integer', 'min:1'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:1000'],
            'chunk_size' => ['required', 'integer', 'min:1048576', 'max:10485760'], // 1MB-10MB
            'mime_type' => ['required', 'string'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $media = Media::create([
            'uploaded_by' => auth()->id(),
            'original_filename' => $request->filename,
            'mime_type' => $request->mime_type,
            'media_type' => FileUploadService::mediaTypeFromMime($request->mime_type),
            'size_bytes' => $request->total_size,
            'disk' => config('filesystems.media_disk', 'public'),
            'alt_text' => $request->alt_text,
            'chunk_size' => $request->chunk_size,
            'total_chunks' => $request->total_chunks,
            'uploaded_chunks' => 0,
            'status' => 'uploading',
        ]);

        // Create temp directory for chunks
        $disk = Storage::disk(config('filesystems.media_disk', 'public'));
        $tempDir = "uploads/chunks/{$media->id}";
        $disk->makeDirectory($tempDir);

        return response()->json([
            'media_id' => $media->id,
            'upload_url' => route('admin.media.chunk.upload'),
            'complete_url' => route('admin.media.chunk.complete'),
        ]);
    }

    /**
     * Upload a single chunk.
     */
    public function uploadChunk(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file'],
        ]);

        $media = Media::findOrFail($request->media_id);
        
        abort_if($media->status !== 'uploading', 400, 'Upload not in progress');

        $disk = Storage::disk(config('filesystems.media_disk', 'public'));
        $chunkPath = "uploads/chunks/{$media->id}/chunk_{$request->chunk_index}";
        
        $disk->put($chunkPath, file_get_contents($request->file('chunk')->getRealPath()));

        $media->increment('uploaded_chunks');

        return response()->json([
            'uploaded_chunks' => $media->fresh()->uploaded_chunks,
            'progress' => round(($media->fresh()->uploaded_chunks / $media->total_chunks) * 100, 2),
        ]);
    }

    /**
     * Complete chunked upload and assemble file.
     */
    public function completeChunkedUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:23'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0', 'max:59.99'],
        ]);

        $media = Media::findOrFail($request->media_id);
        
        abort_if($media->status !== 'uploading', 400, 'Upload not in progress');
        abort_if($media->uploaded_chunks !== $media->total_chunks, 400, 'Not all chunks uploaded');

        $disk = Storage::disk(config('filesystems.media_disk', 'public'));
        $tempDir = "uploads/chunks/{$media->id}";
        $finalPath = "uploads/movies/{$media->id}_{$media->original_filename}";

        // Assemble chunks
        $handle = $disk->open($finalPath, 'w');
        
        for ($i = 0; $i < $media->total_chunks; $i++) {
            $chunkPath = "{$tempDir}/chunk_{$i}";
            $chunkContent = $disk->get($chunkPath);
            $disk->write($handle, $chunkContent);
        }
        $disk->close($handle);

        // Clean up temp chunks
        $disk->deleteDirectory($tempDir);

        // Get video dimensions and duration if video
        $duration = $this->durationFromParts(request());
        $filePath = $disk->path($finalPath);
        
        $updateData = [
            'path' => $finalPath,
            'size_bytes' => $disk->size($finalPath),
            'status' => 'ready',
            'duration' => $duration,
        ];

        if ($media->isVideo()) {
            $info = @getimagesize($filePath); // For video, we can't get dimensions easily via getimagesize
            // Use FFmpeg if available for duration
            if ($duration === null && extension_loaded('ffmpeg')) {
                // Could use FFmpeg here for accurate duration
            }
        }

        $media->update($updateData);

        // Clean up temp if needed
        return response()->json([
            'media' => $media->fresh(),
            'message' => 'Upload completed successfully',
        ]);
    }

    /**
     * Cancel chunked upload and clean up.
     */
    public function cancelChunkedUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ]);

        $media = Media::findOrFail($request->media_id);
        
        $disk = Storage::disk(config('filesystems.media_disk', 'public'));
        $tempDir = "uploads/chunks/{$media->id}";
        
        if ($disk->exists($tempDir)) {
            $disk->deleteDirectory($tempDir);
        }
        
        $media->delete();

        return response()->json(['message' => 'Upload cancelled']);
    }

    public function destroy(Media $media): RedirectResponse
    {
        if ($media->isReferenced()) {
            return back()->with('error', 'This file is still referenced by other records and cannot be deleted.');
        }

        $this->fileService->delete($media);

        return back()->with('success', 'File deleted successfully.');
    }
}
