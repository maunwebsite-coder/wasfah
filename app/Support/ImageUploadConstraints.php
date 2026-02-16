<?php

namespace App\Support;

use Illuminate\Support\Str;

class ImageUploadConstraints
{
    private const DEFAULT_ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Get the MIME types allowed for user-uploaded images.
     */
    public static function allowedMimeTypes(): array
    {
        $mimes = config('content_moderation.image.allowed_mime_types', self::DEFAULT_ALLOWED_MIME_TYPES);

        return array_values(array_unique(array_filter($mimes)));
    }

    /**
     * Convert allowed MIME types into extensions understood by Laravel's mimes rule.
     */
    public static function allowedExtensions(): array
    {
        $extensions = [];

        foreach (self::allowedMimeTypes() as $mime) {
            if (str_contains($mime, '/')) {
                $extensions[] = strtolower((string) Str::after($mime, '/'));
            } else {
                $extensions[] = strtolower(trim($mime));
            }
        }

        // Laravel users often expect jpg alongside jpeg, so force-add it.
        $extensions[] = 'jpg';

        return array_values(array_unique(array_filter($extensions)));
    }

    /**
     * Return a human readable list of allowed extensions.
     */
    public static function allowedExtensionsList(): string
    {
        return implode(', ', array_map(static fn ($extension) => strtoupper($extension), self::allowedExtensions()));
    }

    /**
     * Fetch the max size in kilobytes.
     */
    public static function maxKilobytes(): int
    {
        return (int) config('content_moderation.image.max_kilobytes', 25600);
    }

    /**
     * Convert the KB limit to megabytes for display.
     */
    public static function maxMegabytes(): string
    {
        return self::formatMegabytes(self::maxKilobytes());
    }

    /**
     * Base validation rules (without nullable).
     *
     * @return array<int, string>
     */
    public static function rules(): array
    {
        return [
            'image',
            'mimes:' . implode(',', self::allowedExtensions()),
            'max:' . self::maxKilobytes(),
        ];
    }

    /**
     * Build localized validation messages for a specific attribute.
     *
     * @param  string  $attribute  validation key (e.g. avatar)
     * @param  array<string, string>|string|null  $label localized label(s)
     * @return array<string, string>
     */
    public static function messages(string $attribute, array|string|null $label = null): array
    {
        $locale = app()->getLocale();
        $resolvedLabel = self::resolveLabel($label, $locale);
        $allowed = self::allowedExtensionsList();
        $max = self::maxMegabytes();

        if ($locale === 'ar') {
            return [
                "{$attribute}.image" => "{$resolvedLabel} يجب أن تكون ملف صورة صالح.",
                "{$attribute}.mimes" => "{$resolvedLabel} يجب أن تكون بإحدى الصيغ التالية: {$allowed}.",
                "{$attribute}.max" => "{$resolvedLabel} يجب ألا يتجاوز حجمها {$max} ميجابايت.",
            ];
        }

        return [
            "{$attribute}.image" => "The {$resolvedLabel} must be a valid image file.",
            "{$attribute}.mimes" => "The {$resolvedLabel} must be one of the following types: {$allowed}.",
            "{$attribute}.max" => "The {$resolvedLabel} may not be larger than {$max} MB.",
        ];
    }

    /**
     * Convenience helper to build messages for many attributes at once.
     *
     * @param  array<int, string>|array<string, array|string>  $fields
     */
    public static function messagesFor(array $fields): array
    {
        $messages = [];

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                $field = $value;
                $label = null;
            } else {
                $field = $key;
                $label = $value;
            }

            $messages = array_merge($messages, self::messages($field, $label));
        }

        return $messages;
    }

    private static function resolveLabel(array|string|null $label, string $locale): string
    {
        if (is_array($label)) {
            if (isset($label[$locale])) {
                return (string) $label[$locale];
            }

            if (isset($label['en'])) {
                return (string) $label['en'];
            }

            if (isset($label['ar'])) {
                return (string) $label['ar'];
            }
        } elseif (is_string($label) && $label !== '') {
            return $label;
        }

        return $locale === 'ar' ? 'الصورة' : 'image';
    }

    private static function formatMegabytes(int $kilobytes): string
    {
        $megabytes = $kilobytes / 1024;

        if (abs($megabytes - round($megabytes)) < 0.01) {
            return (string) (int) round($megabytes);
        }

        return number_format($megabytes, 1);
    }
}
