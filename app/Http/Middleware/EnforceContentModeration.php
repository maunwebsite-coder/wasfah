<?php

namespace App\Http\Middleware;

use App\Services\ContentModerationService;
use App\Services\EnhancedImageUploadService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnforceContentModeration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldCheck($request)) {
            return $next($request);
        }

        $errors = [];
        $blockedKeys = config('content_moderation.excluded_input_keys', []);
        $compressedTempPaths = [];

        $maxKilobytes = (int) config('content_moderation.image.max_kilobytes', 25600);
        $maxBytes = $maxKilobytes * 1024;
        $processing = config('content_moderation.image.processing', []);
        $targetQuality = (int) ($processing['quality'] ?? 85);
        $targetMaxWidth = (int) ($processing['max_width'] ?? 1200);
        $targetMaxHeight = (int) ($processing['max_height'] ?? 1200);

        foreach ($this->stringInputs($request) as $key => $value) {
            if ($this->isExcludedKey($key, $blockedKeys)) {
                continue;
            }

            if (ContentModerationService::containsProhibitedLanguage($value)) {
                $errors[$this->formatAttribute($key)][] = 'المحتوى يحتوي على كلمات غير لائقة، يرجى التعديل قبل الإرسال.';
            }
        }

        foreach ($this->allUploadedFiles($request->allFiles()) as $key => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (!$file->isValid()) {
                $errors[$this->formatAttribute($key)][] = 'فشل رفع الملف، يرجى المحاولة مرة أخرى.';
                continue;
            }

            $mimeType = $file->getMimeType() ?? '';

            if (!Str::startsWith($mimeType, 'image/')) {
                continue;
            }

            $allowedMimes = config('content_moderation.image.allowed_mime_types', []);

            if (!in_array($mimeType, $allowedMimes, true)) {
                $errors[$this->formatAttribute($key)][] = 'صيغة الصورة غير مدعومة. يرجى استخدام JPG أو PNG أو GIF أو WebP.';
                continue;
            }

            $preparedFile = $file;

            if ($file->getSize() > $maxBytes) {
                $compression = EnhancedImageUploadService::compressUploadedFile(
                    $file,
                    $maxBytes,
                    $targetQuality,
                    $targetMaxWidth,
                    $targetMaxHeight
                );

                if (!$compression['success'] || empty($compression['file'])) {
                    $errors[$this->formatAttribute($key)][] = $compression['error']
                        ?? 'تعذر ضغط الصورة لتتناسب مع الحجم المسموح.';
                    continue;
                }

                if ($compression['file'] instanceof UploadedFile) {
                    $preparedFile = $compression['file'];
                    $this->replaceUploadedFile($request, $key, $preparedFile);
                    $cleanupPath = $compression['temp_path'] ?? $preparedFile->getRealPath();

                    if ($cleanupPath) {
                        $compressedTempPaths[] = $cleanupPath;
                    }

                    $mimeType = $preparedFile->getMimeType() ?? $mimeType;
                }
            }

            if (ContentModerationService::imageAppearsExplicit($preparedFile)) {
                $errors[$this->formatAttribute($key)][] = 'تم رفض الصورة لأنها قد تحتوي على محتوى غير لائق.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        if (!empty($compressedTempPaths)) {
            app()->terminating(function () use ($compressedTempPaths): void {
                foreach ($compressedTempPaths as $path) {
                    if ($path && is_file($path)) {
                        @unlink($path);
                    }
                }
            });
        }

        return $next($request);
    }

    /**
     * Determine if the request should be scanned.
     */
    protected function shouldCheck(Request $request): bool
    {
        if (!config('content_moderation.enabled', true)) {
            return false;
        }

        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    /**
     * Flatten all string-based inputs.
     *
     * @return array<string, string>
     */
    protected function stringInputs(Request $request): array
    {
        return collect(Arr::dot($request->input()))
            ->filter(fn ($value) => is_string($value))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();
    }

    /**
     * Iterate over all uploaded files, including nested arrays.
     *
     * @param array $files
     * @param string $prefix
     * @return iterable<string, UploadedFile>
     */
    protected function allUploadedFiles(array $files, string $prefix = ''): iterable
    {
        foreach ($files as $key => $value) {
            $field = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                yield from $this->allUploadedFiles($value, $field);
                continue;
            }

            if ($value instanceof UploadedFile) {
                yield $field => $value;
            }
        }
    }

    /**
     * Check whether any segment in the dotted key is excluded.
     */
    protected function isExcludedKey(string $key, array $excluded): bool
    {
        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            if (in_array($segment, $excluded, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format the attribute name for validation errors.
     */
    protected function formatAttribute(string $key): string
    {
        return str_replace('.', ' ', $key);
    }

    /**
     * استبدال ملف مرفوع داخل الطلب بعد ضغطه.
     */
    protected function replaceUploadedFile(Request $request, string $key, UploadedFile $file): void
    {
        $files = $request->files->all();
        Arr::set($files, $key, $file);
        $request->files->replace($files);
    }
}
