<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class ContentModerationService
{
    /**
     * Check if the provided text contains prohibited language.
     */
    public static function containsProhibitedLanguage(?string $text): bool
    {
        if (!filled($text) || !static::isEnabled()) {
            return false;
        }

        $normalized = Str::of($text)
            ->lower()
            ->replaceMatches('/[^\p{L}\p{N}\s]+/u', ' ')
            ->squish()
            ->toString();

        foreach (static::bannedTerms() as $term) {
            if (Str::contains($normalized, $term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempt to detect explicit imagery by checking skin-tone dominance.
     * Returns true when the image looks potentially explicit.
     */
    public static function imageAppearsExplicit(UploadedFile $file): bool
    {
        if (!static::isEnabled() || !Config::get('content_moderation.image.review.enabled', true)) {
            return false;
        }

        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return false;
        }

        $path = $file->getRealPath();

        if (!$path || !is_readable($path)) {
            return false;
        }

        try {
            $contents = file_get_contents($path);
            if ($contents === false) {
                return false;
            }

            $image = @imagecreatefromstring($contents);
            if (!$image) {
                return false;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            if ($width === 0 || $height === 0) {
                imagedestroy($image);
                return false;
            }

            $sampleConfig = Config::get('content_moderation.image.review', []);
            $sampleTarget = (int) ($sampleConfig['sample_target'] ?? 2000);
            $threshold = (float) ($sampleConfig['skin_tone_threshold'] ?? 0.38);

            $totalPixels = $width * $height;
            $samples = min($sampleTarget, $totalPixels);

            if ($samples <= 0) {
                imagedestroy($image);
                return false;
            }

            $skinPixels = 0;

            for ($i = 0; $i < $samples; $i++) {
                $x = random_int(0, $width - 1);
                $y = random_int(0, $height - 1);

                $rgb = imagecolorat($image, $x, $y);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if (static::isSkinTone($r, $g, $b)) {
                    $skinPixels++;
                }
            }

            imagedestroy($image);

            $ratio = $skinPixels / $samples;

            return $ratio > $threshold;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Determine if a pixel falls in common skin-tone ranges.
     * Based on simple heuristic thresholds in RGB space.
     */
    protected static function isSkinTone(int $r, int $g, int $b): bool
    {
        if ($r < 40 || $g < 20 || $b < 20) {
            return false;
        }

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        if ($max - $min < 15) {
            return false;
        }

        $rg = $r - $g;
        $rb = $r - $b;

        return $r > $g && $r > $b && $rg > 15 && $rb > 15 && $rg <= 70 && $rb <= 85;
    }

    /**
     * List of banned terms in Arabic and English.
     */
    protected static function bannedTerms(): array
    {
        $default = [
            'لعنة',
            'قذر',
            'حقير',
            'قحبة',
            'شرموطة',
            'زب',
            'كس',
            'fuck',
            'shit',
            'bitch',
            'slut',
            'porn',
            'xxx',
            'nude',
            'naked',
            'whore',
            'sexy',
        ];

        $configured = Config::get('content_moderation.blocked_terms', []);

        return array_values(array_unique(array_filter(array_merge($configured, $default))));
    }

    /**
     * Determine if the moderation checks are enabled.
     */
    protected static function isEnabled(): bool
    {
        return (bool) Config::get('content_moderation.enabled', true);
    }
}
