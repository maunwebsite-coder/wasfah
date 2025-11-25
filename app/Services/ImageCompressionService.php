<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    private const DEFAULT_MAX_WIDTH = 1920;
    private const DEFAULT_MAX_HEIGHT = 1920;
    private const DEFAULT_QUALITY = 80;

    /**
     * التحقق من إمكانيات النظام
     *
     * @return array
     */
    public static function checkSystemCapabilities(): array
    {
        return [
            'gd_loaded' => extension_loaded('gd'),
            'gd_version' => extension_loaded('gd') ? gd_info()['GD Version'] : null,
            'supported_formats' => [
                'jpeg' => function_exists('imagecreatefromjpeg'),
                'png' => function_exists('imagecreatefrompng'),
                'gif' => function_exists('imagecreatefromgif'),
                'webp' => function_exists('imagecreatefromwebp'),
            ],
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }
    /**
     * ضغط الصورة وحفظها
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality
     * @param int $maxWidth
     * @param int $maxHeight
     * @param object|null $model
     * @return string|null
     */
    public static function compressAndStore(
        UploadedFile $file,
        string $directory = 'images',
        int $quality = self::DEFAULT_QUALITY,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT,
        $model = null
    ): ?string {
        $imageResource = null;
        $optimizedImage = null;
        $tempPath = null;

        try {
            if (!extension_loaded('gd') || !function_exists('imagewebp')) {
                \Log::warning('GD/WebP not available; storing original file without optimization.');
                return $file->store($directory, 'public');
            }

            $mime = $file->getMimeType() ?: '';
            if (!self::isSupportedMime($mime)) {
                throw new \InvalidArgumentException('نوع الملف غير مدعوم');
            }

            $imageResource = self::createImageResource($file);
            if (!$imageResource) {
                throw new \RuntimeException('فشل في قراءة الصورة');
            }

            $originalWidth = imagesx($imageResource);
            $originalHeight = imagesy($imageResource);

            $newDimensions = self::calculateNewDimensions(
                $originalWidth,
                $originalHeight,
                $maxWidth,
                $maxHeight
            );

            $optimizedImage = imagecreatetruecolor(
                $newDimensions['width'],
                $newDimensions['height']
            );

            if (self::supportsAlpha($mime)) {
                imagealphablending($optimizedImage, false);
                imagesavealpha($optimizedImage, true);
                $transparent = imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
                imagefill($optimizedImage, 0, 0, $transparent);
            } else {
                $background = imagecolorallocate($optimizedImage, 255, 255, 255);
                imagefill($optimizedImage, 0, 0, $background);
            }

            imagecopyresampled(
                $optimizedImage,
                $imageResource,
                0,
                0,
                0,
                0,
                $newDimensions['width'],
                $newDimensions['height'],
                $originalWidth,
                $originalHeight
            );

            $tempPath = self::writeWebpTempFile($optimizedImage, $quality);

            $filename = Str::uuid() . '.webp';
            $relativePath = trim($directory, '/') . '/' . $filename;

            $stream = fopen($tempPath, 'rb');
            if (!$stream) {
                throw new \RuntimeException('فشل في قراءة الملف المؤقت بعد التحويل إلى WebP.');
            }

            try {
                if (!Storage::disk('public')->put($relativePath, $stream)) {
                    throw new \RuntimeException('تعذر حفظ الصورة بعد التحويل إلى WebP.');
                }
            } finally {
                fclose($stream);
            }

            return $relativePath;
        } catch (\Throwable $e) {
            \Log::error('خطأ في ضغط الصورة: ' . $e->getMessage());
            return null;
        } finally {
            if ($imageResource instanceof \GdImage || is_resource($imageResource)) {
                imagedestroy($imageResource);
            }

            if ($optimizedImage instanceof \GdImage || is_resource($optimizedImage)) {
                imagedestroy($optimizedImage);
            }

            if ($tempPath && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
                    break;
            }

            // رفع الملف المضغوط إلى التخزين
            $storedPath = Storage::disk('public')->putFileAs(
                $directory,
                new \Illuminate\Http\File($tempPath),
                $filename
            );

            // تسجيل معلومات الضغط في النموذج إذا كان متوفراً
            if ($model && method_exists($model, 'update')) {
                $originalSize = $file->getSize();
                $compressedSize = filesize($tempPath);
                
                $model->update([
                    'image_compressed' => true,
                    'original_image_size' => $originalSize,
                    'compressed_image_size' => $compressedSize,
                    'image_compressed_at' => now(),
                ]);
            }

            // تنظيف الذاكرة
            imagedestroy($image);
            imagedestroy($compressedImage);
            unlink($tempPath);

            return $storedPath;

        } catch (\Exception $e) {
            \Log::error('خطأ في ضغط الصورة: ' . $e->getMessage());
            return null;
        }
    }

    private static function calculateNewDimensions(
        int $originalWidth,
        int $originalHeight,
        int $maxWidth,
        int $maxHeight
    ): array {
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return [
                'width' => $originalWidth,
                'height' => $originalHeight
            ];
        }

        $widthRatio = $maxWidth / max(1, $originalWidth);
        $heightRatio = $maxHeight / max(1, $originalHeight);
        $ratio = min($widthRatio, $heightRatio);

        return [
            'width' => max(1, (int) round($originalWidth * $ratio)),
            'height' => max(1, (int) round($originalHeight * $ratio))
        ];
    }

    private static function isSupportedMime(?string $mime): bool
    {
        if (!$mime) {
            return false;
        }

        return in_array(strtolower($mime), [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-ms-bmp',
        ], true);
    }

    private static function supportsAlpha(string $mime): bool
    {
        return in_array(strtolower($mime), ['image/png', 'image/gif', 'image/webp'], true);
    }

    private static function createImageResource(UploadedFile $file)
    {
        $path = $file->getRealPath() ?: $file->getPathname();

        if (!$path || !is_readable($path)) {
            return null;
        }

        $mime = strtolower($file->getMimeType() ?: '');

        return match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : null,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'image/bmp', 'image/x-ms-bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : null,
            default => function_exists('imagecreatefromstring') ? @imagecreatefromstring(file_get_contents($path)) : null,
        };
    }

    private static function writeWebpTempFile($image, int $quality): string
    {
        $quality = max(10, min(100, $quality));

        $tempBase = tempnam(sys_get_temp_dir(), 'wasfah_webp_');
        if ($tempBase === false) {
            throw new \RuntimeException('تعذر إنشاء ملف مؤقت للصورة.');
        }

        $tempPath = $tempBase . '.webp';
        if (!@rename($tempBase, $tempPath)) {
            $tempPath = $tempBase;
        }

        if (!imagewebp($image, $tempPath, $quality)) {
            @unlink($tempPath);
            throw new \RuntimeException('فشل تحويل الصورة إلى WebP.');
        }

        return $tempPath;
    }

    /**
     * ضغط صورة من URL
     *
     * @param string $imageUrl
     * @param string $directory
     * @param int $quality
     * @param int $maxWidth
     * @param int $maxHeight
     * @return string|null
     */
    public static function compressFromUrl(
        string $imageUrl,
        string $directory = 'images',
        int $quality = self::DEFAULT_QUALITY,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT
    ): ?string {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
                        'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                        'Accept-Language: en-US,en;q=0.9'
                    ]
                ]
            ]);

            $imageData = @file_get_contents($imageUrl, false, $context);
            if (!$imageData) {
                throw new \Exception('فشل في تحميل الصورة من URL');
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'downloaded_image_');
            if ($tempFile === false) {
                throw new \RuntimeException('تعذر إنشاء ملف مؤقت لتحميل الصورة.');
            }

            file_put_contents($tempFile, $imageData);

            try {
                $uploadedFile = new UploadedFile(
                    $tempFile,
                    basename(parse_url($imageUrl, PHP_URL_PATH) ?: Str::uuid() . '.jpg'),
                    mime_content_type($tempFile) ?: null,
                    null,
                    true
                );

                return self::compressAndStore($uploadedFile, $directory, $quality, $maxWidth, $maxHeight);
            } finally {
                if (is_file($tempFile)) {
                    @unlink($tempFile);
                }
            }
        } catch (\Exception $e) {
            \Log::error('خطأ في ضغط الصورة من URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * حذف الصورة المضغوطة
     *
     * @param string $imagePath
     * @return bool
     */
    public static function deleteCompressedImage(string $imagePath): bool
    {
        try {
            if (Storage::disk('public')->exists($imagePath)) {
                return Storage::disk('public')->delete($imagePath);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('خطأ في حذف الصورة: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على معلومات الصورة المضغوطة
     *
     * @param string $imagePath
     * @return array|null
     */
    public static function getImageInfo(string $imagePath): ?array
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return null;
            }

            $imageSize = getimagesize($fullPath);
            $fileSize = filesize($fullPath);

            return [
                'width' => $imageSize[0],
                'height' => $imageSize[1],
                'mime_type' => $imageSize['mime'],
                'file_size' => $fileSize,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2)
            ];
        } catch (\Exception $e) {
            \Log::error('خطأ في الحصول على معلومات الصورة: ' . $e->getMessage());
            return null;
        }
    }
}
