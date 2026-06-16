<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EnhancedImageUploadService
{
    /**
     * رفع صورة مع التأكد من ضبط الحجم الأقصى والمحتوى.
     */
    public static function uploadImage(
        UploadedFile $file,
        string $directory = 'images',
        int $quality = 80,
        int $maxWidth = 1920,
        int $maxHeight = 1920
    ): array {
        try {
            $gdAvailable = extension_loaded('gd')
                && function_exists('imagecreatetruecolor')
                && function_exists('imagecreatefromstring');

            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'الملف المرفوع غير صالح',
                    'error_code' => $file->getError(),
                    'error_message' => $file->getErrorMessage(),
                ];
            }

            $allowedMimes = config('content_moderation.image.allowed_mime_types', [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
            ]);

            $mimeType = $file->getMimeType() ?? '';
            if (!in_array($mimeType, $allowedMimes, true)) {
                return [
                    'success' => false,
                    'error' => 'نوع الملف غير مدعوم. يرجى اختيار صورة بصيغة JPG أو PNG أو GIF أو WebP.',
                    'mime_type' => $mimeType,
                ];
            }

            $maxKilobytes = (int) config('content_moderation.image.max_kilobytes', 25600);
            $maxBytes = $maxKilobytes * 1024;

            if (!$gdAvailable) {
                Log::warning('GD extension not available, storing workshop image without compression.', [
                    'directory' => $directory,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);

                return self::storeWithoutCompression($file, $directory, 'gd_extension_missing');
            }

            $prepared = self::compressUploadedFile($file, $maxBytes, $quality, $maxWidth, $maxHeight);
            if (!$prepared['success']) {
                Log::warning('Image compression failed, falling back to direct storage.', [
                    'error' => $prepared['error'] ?? null,
                    'directory' => $directory,
                    'file_name' => $file->getClientOriginalName(),
                ]);

                return self::storeWithoutCompression($file, $directory, 'compression_failed', $prepared['error'] ?? null);
            }

            $uploadFile = $prepared['file'];
            $extension = $prepared['extension'] ?? self::guessExtension($file);
            $filename = Str::uuid() . '.' . $extension;

            $storedPath = Storage::disk('public')->putFileAs($directory, $uploadFile, $filename);

            if (($prepared['was_compressed'] ?? false) && ($uploadFile->getRealPath())) {
                $realPath = $uploadFile->getRealPath();
                if ($realPath && is_file($realPath)) {
                    @unlink($realPath);
                }
            }

            if ($storedPath) {
                return [
                    'success' => true,
                    'path' => $storedPath,
                    'compressed' => (bool) ($prepared['was_compressed'] ?? false),
                    'original_size' => $file->getSize(),
                    'compressed_size' => $prepared['size'] ?? Storage::disk('public')->size($storedPath),
                    'mime_type' => $prepared['mime'] ?? $mimeType,
                ];
            }

            return [
                'success' => false,
                'error' => 'فشل في حفظ الملف',
            ];
        } catch (\Throwable $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            return [
                'success' => false,
                'error' => 'حدث خطأ غير متوقع أثناء رفع الصورة',
                'exception' => $e->getMessage(),
            ];
        }
    }

    /**
     * ضغط ملف مرفوع (إن لزم) ليصبح تحت الحد المسموح.
     */
    public static function compressUploadedFile(
        UploadedFile $file,
        int $maxBytes,
        int $quality = 80,
        int $maxWidth = 1920,
        int $maxHeight = 1920
    ): array {
        $gdAvailable = extension_loaded('gd')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecreatefromstring');

        if (!$gdAvailable) {
            $maxMb = round($maxBytes / 1024 / 1024, 1);
            return [
                'success' => false,
                'error' => "تعذر تصغير حجم الصورة على الخادم. رجاءً قلل حجم الملف ليكون تحت {$maxMb}MB أو فعّل دعم الصور (GD).",
            ];
        }

        $extension = self::guessExtension($file);
        $mimeType = $file->getMimeType();

        $attempts = self::compressionAttempts($file, $quality, $maxWidth, $maxHeight);
        $lastError = null;

        foreach ($attempts as $attempt) {
            $temp = self::createCompressedTempFile(
                $file,
                $attempt['quality'],
                $attempt['max_width'],
                $attempt['max_height'],
                $attempt['format'] ?? null
            );

            if (!$temp['success']) {
                $lastError = $temp['error'] ?? $lastError;
                continue;
            }

            if ($temp['size'] <= $maxBytes) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'image';
                $newOriginalName = $originalName . '.' . $temp['extension'];

                $compressedFile = new UploadedFile(
                    $temp['temp_path'],
                    $newOriginalName,
                    $temp['mime'],
                    0,
                    true
                );

                return [
                    'success' => true,
                    'file' => $compressedFile,
                    'size' => $temp['size'],
                    'extension' => $temp['extension'],
                    'mime' => $temp['mime'],
                    'was_compressed' => true,
                    'temp_path' => $temp['temp_path'],
                ];
            }

            if (isset($temp['temp_path']) && is_file($temp['temp_path'])) {
                @unlink($temp['temp_path']);
            }
        }

        return [
            'success' => false,
            'error' => $lastError ?? 'تعذر ضغط الصورة لتتناسب مع الحجم المسموح (2 ميجابايت).',
        ];
    }

    /**
     * حفظ الصورة بدون ضغط عند عدم توفر GD أو عند فشل الضغط.
     */
    protected static function storeWithoutCompression(
        UploadedFile $file,
        string $directory,
        string $reason,
        ?string $details = null
    ): array {
        try {
            $extension = self::guessExtension($file);
            $filename = Str::uuid() . '.' . $extension;

            $storedPath = Storage::disk('public')->putFileAs($directory, $file, $filename);

            if ($storedPath) {
                return [
                    'success' => true,
                    'path' => $storedPath,
                    'compressed' => false,
                    'fallback_used' => true,
                    'fallback_reason' => $reason,
                    'fallback_details' => $details,
                    'original_size' => $file->getSize(),
                    'compressed_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'note' => 'تم حفظ الصورة بدون ضغط بسبب نقص إمكانيات الخادم.',
                ];
            }

            return [
                'success' => false,
                'error' => 'تعذر حفظ الصورة بدون ضغط.',
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to store image without compression', [
                'error' => $e->getMessage(),
                'reason' => $reason,
                'details' => $details,
                'file_name' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'error' => 'حدث خطأ أثناء حفظ الصورة بدون ضغط.',
            ];
        }
    }

    /**
     * إعداد محاولات الضغط (الجودة + التصغير).
     */
    protected static function compressionAttempts(UploadedFile $file, int $quality, int $maxWidth, int $maxHeight): array
    {
        $qualityLevels = array_values(array_unique([
            max(10, min(95, $quality)),
            85,
            75,
            65,
            55,
            45,
            40,
            35,
            30,
        ]));

        $scales = [1.0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.45, 0.4, 0.35, 0.3];

        $attempts = [];

        foreach ($scales as $index => $scale) {
            $qualityIndex = min($index, count($qualityLevels) - 1);
            $attempts[] = [
                'quality' => $qualityLevels[$qualityIndex],
                'max_width' => max(280, (int) round($maxWidth * $scale)),
                'max_height' => max(280, (int) round($maxHeight * $scale)),
                'format' => null,
            ];
        }

        if (in_array($file->getMimeType(), ['image/png', 'image/gif'], true)) {
            $attempts[] = [
                'quality' => 75,
                'max_width' => max(260, (int) round($maxWidth * 0.8)),
                'max_height' => max(260, (int) round($maxHeight * 0.8)),
                'format' => 'jpeg',
            ];
            $attempts[] = [
                'quality' => 65,
                'max_width' => max(240, (int) round($maxWidth * 0.7)),
                'max_height' => max(240, (int) round($maxHeight * 0.7)),
                'format' => 'jpeg',
            ];
        }

        return $attempts;
    }

    /**
     * إنشاء ملف مضغوط مؤقت وفقاً للتهيئة المعطاة.
     */
    protected static function createCompressedTempFile(
        UploadedFile $file,
        int $quality,
        int $maxWidth,
        int $maxHeight,
        ?string $forceFormat = null
    ): array {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagecreatetruecolor')) {
            return [
                'success' => false,
                'error' => 'لا يمكن ضغط الصورة على الخادم حالياً. يرجى تقليل حجم الصورة وإعادة المحاولة.',
            ];
        }

        try {
            $path = $file->getPathname();
            if (!$path || !is_readable($path)) {
                return [
                    'success' => false,
                    'error' => 'تعذر قراءة الملف المؤقت للصورة.',
                ];
            }

            $imageData = @file_get_contents($path);
            if ($imageData === false || $imageData === '') {
                return [
                    'success' => false,
                    'error' => 'تعذر تحميل بيانات الصورة.',
                ];
            }

            $image = @imagecreatefromstring($imageData);
            if (!$image) {
                return [
                    'success' => false,
                    'error' => 'تعذر إنشاء مورد الصورة للضغط.',
                ];
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            $dimensions = self::calculateNewDimensions(
                $originalWidth,
                $originalHeight,
                max(1, $maxWidth),
                max(1, $maxHeight)
            );

            $targetFormat = $forceFormat ?? self::detectFormat($file->getMimeType());
            if (!$targetFormat) {
                $targetFormat = 'jpeg';
            }

            $extension = self::extensionForFormat($targetFormat);
            $mime = self::mimeForFormat($targetFormat);

            $compressedImage = imagecreatetruecolor($dimensions['width'], $dimensions['height']);

            if (in_array($targetFormat, ['png', 'webp', 'gif'], true)) {
                imagealphablending($compressedImage, false);
                imagesavealpha($compressedImage, true);
                $transparent = imagecolorallocatealpha($compressedImage, 255, 255, 255, 127);
                imagefill($compressedImage, 0, 0, $transparent);
            } else {
                $background = imagecolorallocate($compressedImage, 255, 255, 255);
                imagefill($compressedImage, 0, 0, $background);
            }

            imagecopyresampled(
                $compressedImage,
                $image,
                0,
                0,
                0,
                0,
                $dimensions['width'],
                $dimensions['height'],
                $originalWidth,
                $originalHeight
            );

            $tempBase = tempnam(sys_get_temp_dir(), 'wasfah_img_');
            if ($tempBase === false) {
                imagedestroy($image);
                imagedestroy($compressedImage);

                return [
                    'success' => false,
                    'error' => 'تعذر إنشاء ملف مؤقت للضغط.',
                ];
            }

            $tempPath = $tempBase . '.' . $extension;
            if (!@rename($tempBase, $tempPath)) {
                $tempPath = $tempBase;
            }

            $saved = self::writeImage($compressedImage, $tempPath, $targetFormat, $quality);

            imagedestroy($image);
            imagedestroy($compressedImage);

            if (!$saved || !is_file($tempPath)) {
                if (is_file($tempPath)) {
                    @unlink($tempPath);
                }

                return [
                    'success' => false,
                    'error' => 'تعذر إنشاء ملف الصورة المضغوط.',
                ];
            }

            $size = filesize($tempPath);
            if ($size === false) {
                @unlink($tempPath);

                return [
                    'success' => false,
                    'error' => 'تعذر تحديد حجم الملف المضغوط.',
                ];
            }

            return [
                'success' => true,
                'temp_path' => $tempPath,
                'size' => $size,
                'extension' => $extension,
                'mime' => $mime,
            ];
        } catch (\Throwable $e) {
            if (isset($tempPath) && is_file($tempPath)) {
                @unlink($tempPath);
            }

            return [
                'success' => false,
                'error' => 'تعذر ضغط الصورة: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * كتابة الصورة في الملف حسب الصيغة.
     */
    protected static function writeImage($image, string $path, string $format, int $quality): bool
    {
        $quality = max(10, min(95, $quality));

        switch ($format) {
            case 'jpeg':
                if (!function_exists('imagejpeg')) {
                    return false;
                }

                return imagejpeg($image, $path, $quality);

            case 'png':
                if (!function_exists('imagepng')) {
                    return false;
                }

                $compressionLevel = self::pngCompressionLevel($quality);

                return imagepng($image, $path, $compressionLevel);

            case 'gif':
                if (!function_exists('imagegif')) {
                    return false;
                }

                return imagegif($image, $path);

            case 'webp':
                if (!function_exists('imagewebp')) {
                    if (function_exists('imagejpeg')) {
                        return imagejpeg($image, $path, $quality);
                    }

                    return false;
                }

                return imagewebp($image, $path, $quality);

            default:
                return false;
        }
    }

    /**
     * حساب مستوى الضغط لصور PNG بناءً على الجودة.
     */
    protected static function pngCompressionLevel(int $quality): int
    {
        $quality = max(0, min(100, $quality));

        return (int) round((100 - $quality) * 9 / 100);
    }

    /**
     * تحديد صيغة الهدف بناءً على نوع الملف.
     */
    protected static function detectFormat(?string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpeg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };
    }

    /**
     * استنتاج الامتداد من الصيغة.
     */
    protected static function extensionForFormat(string $format): string
    {
        return match ($format) {
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    /**
     * استنتاج نوع المحتوى من الصيغة.
     */
    protected static function mimeForFormat(string $format): string
    {
        return match ($format) {
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    /**
     * استنتاج الامتداد للملف المرفوع.
     */
    protected static function guessExtension(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

        if ($extension === '') {
            $extension = self::extensionForFormat(self::detectFormat($file->getMimeType()) ?? 'jpeg');
        }

        return $extension;
    }

    /**
     * حساب الأبعاد الجديدة مع الحفاظ على النسبة.
     */
    protected static function calculateNewDimensions(
        int $originalWidth,
        int $originalHeight,
        int $maxWidth,
        int $maxHeight
    ): array {
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return [
                'width' => $originalWidth,
                'height' => $originalHeight,
            ];
        }

        $widthRatio = $maxWidth / $originalWidth;
        $heightRatio = $maxHeight / $originalHeight;
        $ratio = min($widthRatio, $heightRatio);

        return [
            'width' => max(1, (int) round($originalWidth * $ratio)),
            'height' => max(1, (int) round($originalHeight * $ratio)),
        ];
    }

    /**
     * حذف الصورة من التخزين العام.
     */
    public static function deleteImage(string $imagePath): bool
    {
        try {
            if (Storage::disk('public')->exists($imagePath)) {
                return Storage::disk('public')->delete($imagePath);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to delete image', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * التحقق من إمكانيات النظام المتعلقة بالصور.
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
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }
}
