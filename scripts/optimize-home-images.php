<?php
/**
 * One-shot optimizer for homepage Lighthouse image offenders (no Laravel/DB boot).
 * Usage: php scripts/optimize-home-images.php
 */
$root = dirname(__DIR__);
$targets = [
    [$root . '/public/store/1/Next-Level-New-Logo-e1656427733314.webp', 860],
    [$root . '/public/store/1/video_thumb.webp', 800],
    [$root . '/public/store/1/in-person-course-3d-icon.webp', 400],
    [$root . '/public/assets/default/img/footer/pattern.png', 800],
    [$root . '/public/assets/default/vendors/flagstrap/css/flags.webp', 1024],
];

// Diploma landing + section icons if present
foreach ([
    $root . '/public/store/1/diplomas-landing',
    $root . '/public/store/1/ايقونات الاقسام',
] as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, ['webp', 'png', 'jpg', 'jpeg'], true)) {
            continue;
        }
        $max = (strpos($dir, 'ايقونات') !== false) ? 128 : 1200;
        $targets[] = [$dir . DIRECTORY_SEPARATOR . $file, $max];
    }
}

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD required\n");
    exit(1);
}

foreach ($targets as [$full, $maxEdge]) {
    if (!is_file($full)) {
        echo "missing: $full\n";
        continue;
    }
    $info = @getimagesize($full);
    if (!$info) {
        echo "unreadable: $full\n";
        continue;
    }
    [$w, $h, $type] = $info;
    $scale = min(1, $maxEdge / max($w, $h));
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));

    // Skip if already small enough on disk and dimensions
    if ($scale >= 1 && filesize($full) < 24 * 1024) {
        echo "skip small: $full\n";
        continue;
    }

    switch ($type) {
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($full);
            break;
        case IMAGETYPE_WEBP:
            $src = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($full) : false;
            break;
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($full);
            break;
        default:
            $src = false;
    }
    if (!$src) {
        echo "decode fail: $full\n";
        continue;
    }

    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $t = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $nw, $nh, $t);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $tmp = $full . '.tmp';
    $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
    $ok = false;
    if ($ext === 'webp' && function_exists('imagewebp')) {
        $ok = imagewebp($dst, $tmp, 80);
    } elseif ($ext === 'png') {
        $ok = imagepng($dst, $tmp, 8);
    } elseif (in_array($ext, ['jpg', 'jpeg'], true)) {
        $ok = imagejpeg($dst, $tmp, 80);
    }

    imagedestroy($src);
    imagedestroy($dst);

    if (!$ok || !is_file($tmp)) {
        @unlink($tmp);
        echo "encode fail: $full\n";
        continue;
    }

    $before = filesize($full);
    $after = filesize($tmp);
    if ($after >= $before && $scale >= 1) {
        unlink($tmp);
        echo "no gain: $full ($before bytes)\n";
        continue;
    }
    // Prefer smaller file even when resizing
    if ($after >= $before) {
        unlink($tmp);
        echo "no gain after resize: $full\n";
        continue;
    }
    rename($tmp, $full);
    echo "ok: " . basename($full) . " {$w}x{$h}->{$nw}x{$nh} {$before}->{$after}\n";
}
