<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaServeController extends Controller
{
    public function serve(string $path): BinaryFileResponse|ResponseHeaderBag
    {
        $disk = config('filesystems.disks.public');
        $fullPath = storage_path('app/public/' . $path);
        
        // Check if file exists
        if (!File::exists($fullPath)) {
            abort(404, 'Media file not found.');
        }
        
        $mimeType = File::mimeType($fullPath);
        
        // For videos, support range requests (streaming)
        if (str_starts_with($mimeType, 'video/') || str_starts_with($mimeType, 'audio/')) {
            return $this->streamFile($fullPath, $mimeType);
        }
        
        // For images and other files, serve directly
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
    
    private function streamFile(string $path, string $mimeType): BinaryFileResponse
    {
        $response = response()->file($path, [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
        
        // Handle range requests for video/audio streaming
        if (request()->hasHeader('Range')) {
            $range = request()->header('Range');
            $fileSize = File::size($path);
            
            $response->setStatusCode(206); // Partial Content
            $response->headers->set('Content-Range', 'bytes 0-' . ($fileSize - 1) . '/' . $fileSize);
            $response->headers->set('Content-Length', $fileSize);
        }
        
        return $response;
    }
}