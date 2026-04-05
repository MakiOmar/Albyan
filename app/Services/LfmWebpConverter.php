<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use UniSharp\LaravelFilemanager\LfmPath;

class LfmWebpConverter
{
    /**
     * After LFM saves an upload, replace JPEG/PNG with WebP when enabled.
     * Returns the filename LFM should expose (may change extension to .webp).
     */
    public function convertAfterUpload(LfmPath $lfm, string $filename): string
    {
        if (! config('lfm.webp.enabled', true)) {
            return $filename;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (! in_array($extension, config('lfm.webp.convert_extensions', ['jpg', 'jpeg', 'png']), true)) {
            return $filename;
        }

        if (! function_exists('imagewebp')) {
            Log::warning('LfmWebpConverter: PHP imagewebp() not available; skipping WebP conversion.');

            return $filename;
        }

        $diskName = config('lfm.disk', 'upload');

        try {
            $disk = Storage::disk($diskName);
        } catch (\Throwable $e) {
            Log::error('LfmWebpConverter: invalid disk.', ['disk' => $diskName, 'exception' => $e->getMessage()]);

            return $filename;
        }

        $relativePath = $lfm->setName($filename)->path('storage');
        $absolutePath = $lfm->setName($filename)->path('absolute');

        // Intervention needs a readable local path; skip for remote-only disks.
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return $filename;
        }

        $quality = (int) config('lfm.webp.quality', 82);
        $quality = max(1, min(100, $quality));

        try {
            $encoded = (string) Image::make($absolutePath)->encode('webp', $quality);
        } catch (\Throwable $e) {
            Log::warning('LfmWebpConverter: encode failed, keeping original.', [
                'path' => $relativePath,
                'exception' => $e->getMessage(),
            ]);

            return $filename;
        }

        $webpRelative = $this->webpRelativePath($relativePath);

        try {
            $disk->put($webpRelative, $encoded, ['visibility' => 'public']);
            $disk->delete($relativePath);
        } catch (\Throwable $e) {
            Log::error('LfmWebpConverter: could not write WebP or delete original.', [
                'relative' => $relativePath,
                'exception' => $e->getMessage(),
            ]);

            return $filename;
        }

        return pathinfo($filename, PATHINFO_FILENAME).'.webp';
    }

    private function webpRelativePath(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $dir = dirname($relativePath);
        $base = pathinfo($relativePath, PATHINFO_FILENAME);
        $webp = $base.'.webp';

        return ($dir === '.' || $dir === '') ? $webp : $dir.'/'.$webp;
    }
}
