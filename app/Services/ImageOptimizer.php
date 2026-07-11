<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    protected int $maxWidth = 1920;
    protected int $maxHeight = 1920;
    protected int $quality = 85;

    public function __construct(?int $quality = null, ?int $maxWidth = null, ?int $maxHeight = null)
    {
        $this->quality = $quality ?? config('image.quality', 85);
        $this->maxWidth = $maxWidth ?? config('image.max_width', 1920);
        $this->maxHeight = $maxHeight ?? config('image.max_height', 1920);
    }

    public function optimize(string $filePath, ?string $disk = 'public'): ?string
    {
        if (!file_exists($filePath)) {
            Log::warning("ImageOptimizer: file not found - {$filePath}");
            return null;
        }

        $info = getimagesize($filePath);
        if (!$info) {
            return null;
        }

        [$width, $height, $type] = $info;
        $mime = $info['mime'] ?? '';

        $supported = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!in_array($type, $supported)) {
            return null;
        }

        $src = $this->createImage($filePath, $type);
        if (!$src) {
            return null;
        }

        // Convert palette-based PNG to true color for WebP support
        if ($type === IMAGETYPE_PNG && imageistruecolor($src) === false) {
            $truecolor = imagecreatetruecolor($width, $height);
            imagealphablending($truecolor, false);
            imagesavealpha($truecolor, true);
            imagecopy($truecolor, $src, 0, 0, 0, 0, $width, $height);
            imagedestroy($src);
            $src = $truecolor;
        }

        [$newWidth, $newHeight] = $this->calculateDimensions($width, $height);

        if ($newWidth !== $width || $newHeight !== $height) {
            $dst = imagescale($src, $newWidth, $newHeight, IMG_BILINEAR_FIXED);
            if ($dst) {
                imagedestroy($src);
                $src = $dst;
            }
        }

        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $filePath);

        ob_start();
        if (!imagewebp($src, null, $this->quality)) {
            ob_end_clean();
            imagedestroy($src);
            Log::warning("ImageOptimizer: WebP conversion failed for {$filePath}");
            return null;
        }
        $webpData = ob_get_clean();

        imagedestroy($src);

        if ($webpData === false || strlen($webpData) === 0) {
            return null;
        }

        file_put_contents($webpPath, $webpData);

        if ($filePath !== $webpPath) {
            @unlink($filePath);
        }

        $relativePath = $this->getRelativePath($webpPath, $disk);

        Log::info("ImageOptimizer: optimized {$filePath} -> {$webpPath} ({$newWidth}x{$newHeight})");

        return $relativePath;
    }

    public function optimizeFromUrl(string $url, ?string $disk = 'public'): ?string
    {
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? '', '/storage/');

        $fullPath = Storage::disk($disk)->path($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        $result = $this->optimize($fullPath, $disk);
        if (!$result) {
            return null;
        }

        // If input was a full URL, return a full URL
        if (str_starts_with($url, 'http')) {
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';
            return "{$scheme}://{$host}/storage/{$result}";
        }

        return $result;
    }

    protected function createImage(string $filePath, int $type): ?\GdImage
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($filePath),
            IMAGETYPE_GIF => @imagecreatefromgif($filePath),
            default => null,
        };
    }

    protected function calculateDimensions(int $width, int $height): array
    {
        if ($width <= $this->maxWidth && $height <= $this->maxHeight) {
            return [$width, $height];
        }

        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
        return [(int) round($width * $ratio), (int) round($height * $ratio)];
    }

    protected function getRelativePath(string $fullPath, string $disk): string
    {
        $root = Storage::disk($disk)->path('');
        $relative = str_replace($root, '', $fullPath);
        return ltrim($relative, '/');
    }
}
