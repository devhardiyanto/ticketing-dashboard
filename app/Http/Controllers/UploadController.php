<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload file to storage (MinIO/S3)
     * Returns the relative path for storage in database
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'folder' => 'nullable|string|max:50',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Generate unique filename
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $folder.'/'.$filename;

        // Store file to S3/MinIO
        Storage::disk('s3')->put($path, file_get_contents($file), 'private');

        return response()->json([
            'success' => true,
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Delete file from storage
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Safety check - prevent directory traversal
        if (Str::contains($path, '..')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
            ], 400);
        }

        // Optimistic delete - S3 delete is idempotent
        Storage::disk('s3')->delete($path);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'File not found',
        ], 404);
    }

    /**
     * Generate signed URL for a single file (API endpoint)
     */
    public function generateSignedUrl(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        $expiry = (int) config('app.signed_url_expiry', 60);

        // Optimistic URL generation to avoid API call and permission issues
        // Validity is checked when accessing the URL

        $url = Storage::disk('s3')->temporaryUrl(
            $path,
            now()->addMinutes($expiry)
        );

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
            'expires_at' => now()->addMinutes($expiry)->toISOString(),
        ]);
    }

    /**
     * Generate signed URLs for multiple files (batch API endpoint)
     */
    public function generateSignedUrls(Request $request)
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'required|string',
        ]);

        $paths = $request->input('paths');
        $expiry = (int) config('app.signed_url_expiry', 60);
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = [
                'success' => true,
                'url' => Storage::disk('s3')->temporaryUrl(
                    $path,
                    now()->addMinutes($expiry)
                ),
                'expires_at' => now()->addMinutes($expiry)->toISOString(),
            ];
        }

        return response()->json([
            'success' => true,
            'urls' => $results,
        ]);
    }

    /**
     * Extract image paths from HTML content (for cleanup)
     */
    public static function extractImagePathsFromHtml(?string $html): array
    {
        if (empty($html)) {
            return [];
        }

        $paths = [];

        // Match data-storage-path attribute
        preg_match_all('/data-storage-path=["\']([^"\']+)["\']/', $html, $matches);
        if (! empty($matches[1])) {
            $paths = array_merge($paths, $matches[1]);
        }

        return array_unique($paths);
    }

    /**
     * Delete multiple files from storage
     */
    public static function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (! empty($path) && ! Str::contains($path, '..')) {
                Storage::disk('s3')->delete($path);
            }
        }
    }
}
