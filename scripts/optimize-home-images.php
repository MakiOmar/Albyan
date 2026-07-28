<?php
/**
 * One-shot optimizer for local public assets (no Laravel boot / DB required).
 * Usage: php scripts/optimize-home-images.php
 */
$root = dirname(__DIR__);
$targets = [
    [$root . '/public/assets/default/img/footer/pattern.png', 800],
    [$root . '/public/assets/default/vendors/flagstrap/css/flags.webp', 1024],
];

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
        echo "encode fail: $full\n";
        continue;
    }

    $before = filesize($full);
    $after = filesize($tmp);
    if ($after >= $before) {
        unlink($tmp);
        echo "no gain: $full ($before bytes)\n";
        continue;
    }
    if (!is_file($full . '.bak')) {
        copy($full, $full . '.bak');
    }
    rename($tmp, $full);
    echo "ok: $full {$w}x{$h}->{$nw}x{$nh} {$before}->{$after}\n";
}
