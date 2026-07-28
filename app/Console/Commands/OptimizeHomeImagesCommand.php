<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Re-encode oversized homepage/store images to ~2x display size (same look, smaller files).
 * Run on the server that holds public/store assets after deploy.
 */
class OptimizeHomeImagesCommand extends Command
{
    protected $signature = 'images:optimize-home
                            {--dry-run : List targets without writing}
                            {--max-edge=1200 : Max width/height for large hero/content images}
                            {--icon-edge=128 : Max edge for small UI icons}';

    protected $description = 'Compress oversized WebP/PNG assets used on the homepage (visual size unchanged via CSS)';

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('PHP GD extension is required.');
            return 1;
        }

        $maxEdge = max(64, (int) $this->option('max-edge'));
        $iconEdge = max(32, (int) $this->option('icon-edge'));
        $dry = (bool) $this->option('dry-run');

        $targets = [
            // Lighthouse oversized offenders (relative to public/)
            ['path' => 'store/1/Next-Level-New-Logo-e1656427733314.webp', 'max' => 860],
            ['path' => 'store/1/video_thumb.webp', 'max' => 800],
            ['path' => 'store/1/diplomas-landing/7.webp', 'max' => $maxEdge],
            ['path' => 'store/1/in-person-course-3d-icon.webp', 'max' => 256],
            ['path' => 'assets/default/img/footer/pattern.png', 'max' => 800],
            ['path' => 'assets/default/vendors/flagstrap/css/flags.webp', 'max' => 1024],
        ];

        // Section icon folder (Arabic path) — resize tiny-display icons
        $iconsDir = public_path('store/1/ايقونات الاقسام');
        if (is_dir($iconsDir)) {
            foreach (File::files($iconsDir) as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['webp', 'png', 'jpg', 'jpeg'], true)) {
                    $targets[] = [
                        'path' => 'store/1/ايقونات الاقسام/' . $file->getFilename(),
                        'max' => $iconEdge,
                    ];
                }
            }
        }

        $saved = 0;
        foreach ($targets as $target) {
            $full = public_path($target['path']);
            if (!is_file($full)) {
                $this->line("skip (missing): {$target['path']}");
                continue;
            }

            $before = filesize($full);
            $result = $this->resizeFile($full, (int) $target['max'], $dry);
            if ($result === null) {
                $this->line("skip (unsupported/unchanged): {$target['path']}");
                continue;
            }

            $after = $dry ? $result['bytes'] : filesize($full);
            $delta = $before - $after;
            $this->info(sprintf(
                '%s %s → ~%s (%s saved)%s',
                $dry ? '[dry]' : 'ok',
                $target['path'],
                $this->formatBytes($after),
                $this->formatBytes(max(0, $delta)),
                isset($result['dims']) ? " [{$result['dims']}]" : ''
            ));
            if ($delta > 0) {
                $saved += $delta;
            }
        }

        $this->info('Total potential/actual savings: ' . $this->formatBytes($saved));
        return 0;
    }

    /**
     * @return array{bytes:int,dims?:string}|null
     */
    private function resizeFile(string $fullPath, int $maxEdge, bool $dry): ?array
    {
        $info = @getimagesize($fullPath);
        if (!$info) {
            return null;
        }

        [$width, $height, $type] = $info;
        if ($width <= $maxEdge && $height <= $maxEdge && filesize($fullPath) < 40 * 1024) {
            return null;
        }

        $scale = min(1, $maxEdge / max($width, $height));
        $newW = max(1, (int) round($width * $scale));
        $newH = max(1, (int) round($height * $scale));

        $src = $this->createImage($fullPath, $type);
        if (!$src) {
            return null;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $tmp = $fullPath . '.opt.tmp';

        $ok = false;
        if ($ext === 'webp' && function_exists('imagewebp')) {
            $ok = imagewebp($dst, $tmp, 82);
        } elseif ($ext === 'png') {
            $ok = imagepng($dst, $tmp, 8);
        } elseif (in_array($ext, ['jpg', 'jpeg'], true)) {
            $ok = imagejpeg($dst, $tmp, 82);
        }

        imagedestroy($src);
        imagedestroy($dst);

        if (!$ok || !is_file($tmp)) {
            @unlink($tmp);
            return null;
        }

        $newSize = filesize($tmp);
        if ($newSize >= filesize($fullPath)) {
            @unlink($tmp);
            return null;
        }

        if ($dry) {
            @unlink($tmp);
            return ['bytes' => $newSize, 'dims' => "{$width}x{$height}->{$newW}x{$newH}"];
        }

        // Backup once
        $backup = $fullPath . '.bak';
        if (!is_file($backup)) {
            @copy($fullPath, $backup);
        }
        rename($tmp, $fullPath);

        return ['bytes' => $newSize, 'dims' => "{$width}x{$height}->{$newW}x{$newH}"];
    }

    private function createImage(string $path, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($path);
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            default:
                return false;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KiB';
        }
        return round($bytes / (1024 * 1024), 2) . ' MiB';
    }
}
