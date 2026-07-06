<?php

function sh_uploads_path(string $subdir = ''): string
{
    $base = dirname(__DIR__) . '/uploads';
    if ($subdir !== '') {
        $base .= '/' . trim(str_replace('\\', '/', $subdir), '/');
    }
    return $base;
}

function sh_uploads_public_url(string $subdir, string $filename): string
{
    $path = 'uploads/' . trim($subdir, '/') . '/' . ltrim($filename, '/');
    return sh_url($path);
}

function sh_ensure_upload_dir(string $subdir): bool
{
    $dir = sh_uploads_path($subdir);
    if (is_dir($dir)) {
        return is_writable($dir);
    }
    if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }
    $index = $dir . '/index.php';
    if (!is_file($index)) {
        @file_put_contents($index, "<?php\nhttp_response_code(403);\nexit;\n");
    }
    return is_writable($dir);
}

/** @return array{ok:bool,url?:string,path?:string,error?:string,width?:int,height?:int} */
function sh_process_uploaded_image(string $tmpPath, string $subdir = 'products', int $maxWidth = 1200, int $quality = 82): array
{
    if (!is_readable($tmpPath)) {
        return ['ok' => false, 'error' => 'Unreadable upload.'];
    }
    if (!sh_ensure_upload_dir($subdir)) {
        return ['ok' => false, 'error' => 'Upload directory not writable.'];
    }

    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return ['ok' => false, 'error' => 'Invalid image file.'];
    }

    $mime = $info['mime'] ?? '';
    $src = match ($mime) {
        'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($tmpPath),
        'image/png' => @imagecreatefrompng($tmpPath),
        'image/gif' => @imagecreatefromgif($tmpPath),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
        default => false,
    };

    if ($src === false) {
        return ['ok' => false, 'error' => 'Unsupported image format.'];
    }

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    if ($srcW < 1 || $srcH < 1) {
        imagedestroy($src);
        return ['ok' => false, 'error' => 'Invalid image dimensions.'];
    }

    $dstW = $srcW;
    $dstH = $srcH;
    if ($srcW > $maxWidth) {
        $dstW = $maxWidth;
        $dstH = (int) round($srcH * ($maxWidth / $srcW));
    }

    $dst = imagecreatetruecolor($dstW, $dstH);
    if ($dst === false) {
        imagedestroy($src);
        return ['ok' => false, 'error' => 'Could not process image.'];
    }

    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
    imagedestroy($src);

    $useWebp = function_exists('imagewebp');
    $ext = $useWebp ? 'webp' : 'jpg';
    $filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fullPath = sh_uploads_path($subdir) . '/' . $filename;

    $saved = $useWebp
        ? @imagewebp($dst, $fullPath, $quality)
        : @imagejpeg($dst, $fullPath, min(92, $quality + 8));

    imagedestroy($dst);

    if (!$saved || !is_file($fullPath)) {
        return ['ok' => false, 'error' => 'Could not save optimized image.'];
    }

    return [
        'ok'     => true,
        'url'    => sh_uploads_public_url($subdir, $filename),
        'path'   => $fullPath,
        'width'  => $dstW,
        'height' => $dstH,
        'format' => $ext,
    ];
}

function sh_delete_uploaded_file(string $url): bool
{
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    $base = sh_url('');
    if (!str_starts_with($url, $base . 'uploads/') && !str_starts_with($url, '/shop/uploads/')) {
        return false;
    }
    $relative = preg_replace('#^.*?uploads/#', 'uploads/', $url);
    $full = dirname(__DIR__) . '/' . $relative;
    $realUploads = realpath(sh_uploads_path());
    $realFile = realpath($full);
    if ($realUploads === false || $realFile === false || !str_starts_with($realFile, $realUploads)) {
        return false;
    }
    return @unlink($realFile);
}