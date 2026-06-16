<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroSlideImageService
{
    private const DIRECTORY = 'hero-slides';
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1920;
    private const QUALITY = 80;
    public const MAX_FILE_SIZE_KB = 25600; // 25MB limit referenced in validation/UI.

    /**
     * Store an optimized hero slide asset.
     */
    public static function storeOptimized(UploadedFile $file): string
    {
        if (self::isVideo($file) || !self::canProcessImage($file)) {
            return $file->store(self::DIRECTORY, 'public');
        }

        $image = null;
        $resized = null;
        $tempPath = null;

        try {
            $image = self::createImageResource($file);

            if (!$image) {
                throw new \RuntimeException('Unable to initialize image resource.');
            }

            $resized = self::resizeImage(
                $image,
                self::MAX_WIDTH,
                self::MAX_HEIGHT,
                self::supportsAlpha($file)
            );

            if (!function_exists('imagewebp')) {
                throw new \RuntimeException('imagewebp is not available.');
            }

            $tempBase = tempnam(sys_get_temp_dir(), 'hero_slide_');
            if ($tempBase === false) {
                throw new \RuntimeException('Unable to allocate temporary file.');
            }

            $tempPath = $tempBase . '.webp';
            if (!@rename($tempBase, $tempPath)) {
                $tempPath = $tempBase;
            }

            if (!imagewebp($resized, $tempPath, self::QUALITY)) {
                throw new \RuntimeException('Failed to encode image as WebP.');
            }

            $filename = Str::uuid() . '.webp';
            $storagePath = self::DIRECTORY . '/' . $filename;

            $stream = fopen($tempPath, 'rb');
            if (!$stream) {
                throw new \RuntimeException('Unable to open WebP temp file.');
            }

            try {
                if (!Storage::disk('public')->put($storagePath, $stream)) {
                    throw new \RuntimeException('Failed to write optimized hero slide.');
                }
            } finally {
                fclose($stream);
            }

            return $storagePath;
        } catch (\Throwable $throwable) {
            Log::warning('Hero slide WebP conversion failed; storing original file.', [
                'message' => $throwable->getMessage(),
                'mime' => $file->getMimeType(),
            ]);

            return $file->store(self::DIRECTORY, 'public');
        } finally {
            if ($image instanceof \GdImage || is_resource($image)) {
                imagedestroy($image);
            }

            if (($resized instanceof \GdImage || is_resource($resized)) && $resized !== $image) {
                imagedestroy($resized);
            }

            if ($tempPath && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    protected static function isVideo(UploadedFile $file): bool
    {
        $mime = $file->getMimeType() ?: '';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

        return $mime === 'video/webm' || $extension === 'webm';
    }

    protected static function canProcessImage(UploadedFile $file): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $mime = $file->getMimeType() ?: '';

        if (!str_starts_with($mime, 'image/')) {
            return false;
        }

        if ($mime === 'image/svg+xml') {
            return false;
        }

        return true;
    }

    protected static function supportsAlpha(UploadedFile $file): bool
    {
        $mime = $file->getMimeType() ?: '';

        return in_array($mime, ['image/png', 'image/gif', 'image/webp'], true);
    }

    protected static function createImageResource(UploadedFile $file)
    {
        $path = $file->getRealPath();

        if (!$path || !is_readable($path)) {
            return null;
        }

        $mime = $file->getMimeType() ?: '';

        return match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : null,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'image/bmp', 'image/x-ms-bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : null,
            default => function_exists('imagecreatefromstring') ? @imagecreatefromstring(file_get_contents($path)) : null,
        };
    }

    protected static function resizeImage($image, int $maxWidth, int $maxHeight, bool $preserveAlpha)
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $widthRatio = $maxWidth / max(1, $originalWidth);
        $heightRatio = $maxHeight / max(1, $originalHeight);
        $ratio = min(1.0, $widthRatio, $heightRatio);

        $targetWidth = max(1, (int) round($originalWidth * $ratio));
        $targetHeight = max(1, (int) round($originalHeight * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($preserveAlpha) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $background = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $background);
        }

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $originalWidth,
            $originalHeight
        );

        return $canvas;
    }
}
