<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected $event_repo;

    public function ifEventNull($event_id = null): bool
    {
        return ! $event_id;
    }

    /**
     * Generate signed URL for a storage path
     */
    protected function getSignedUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Check if it's already a full URL (legacy data)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        try {
            $expiry = (int) config('app.signed_url_expiry', 60);

            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes($expiry));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete file from S3/MinIO storage
     */
    protected function deleteStorageFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        // Skip if it's a full URL (legacy data)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
