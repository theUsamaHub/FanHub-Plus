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
            'files.*' => ['file', 'max:51200'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'numeric', 'min:0', 'max:86400'],
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

        $duration = $request->input('duration') !== null ? (float) $request->input('duration') : null;

        foreach ($request->file('files') as $file) {
            $this->fileService->upload(
                $file,
                'uploads/'.FileUploadService::categoryFromMime($file->getMimeType() ?? ''),
                null,
                null,
                $request->input('alt_text'),
                $duration
            );
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function edit(Media $media): View
    {
        return view('admin.media.edit', compact('media'));
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'numeric', 'min:0', 'max:86400'],
        ]);

        $this->fileService->updateMetadata($media, $request->only(['alt_text', 'duration']));

        return redirect()->route('admin.media.index')
            ->with('success', 'Media updated successfully.');
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
