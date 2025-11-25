<?php

namespace App\Support;

use Illuminate\Support\Str;

class VideoEmbed
{
    /**
     * Determine if the URL points to a direct video file we can play inline.
     */
    public static function inlinePlayable(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $lower = Str::lower($url);
        foreach (['.mp4', '.mov', '.webm'] as $extension) {
            if (Str::contains($lower, $extension)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize common video URLs to embeddable formats when possible.
     */
    public static function normalize(?string $url): ?string
    {
        $clean = self::cleanUrl($url);
        if ($clean === null) {
            return null;
        }

        return self::embedUrl($clean) ?? $clean;
    }

    /**
     * Produce an embeddable URL for supported providers.
     */
    public static function embedUrl(?string $url): ?string
    {
        $clean = self::cleanUrl($url);
        if ($clean === null) {
            return null;
        }

        $lower = Str::lower($clean);

        // Instagram reels/posts
        if (Str::contains($lower, 'instagram.com') || Str::contains($lower, 'instagr.am')) {
            // Escape the delimiter character inside the character class to avoid "unknown modifier" errors.
            if (preg_match('#(?:instagram\\.com|instagr\\.am)/(reel|p|tv)/([^/?\\#&]+)#i', $clean, $matches)) {
                $mediaType = $matches[1];
                $mediaId = $matches[2];
                return sprintf('https://www.instagram.com/%s/%s/embed', $mediaType, $mediaId);
            }
        }

        // LinkedIn post embeds
        if (Str::contains($lower, 'linkedin.com')) {
            if (Str::contains($lower, '/embed/')) {
                return $clean;
            }

            if (preg_match('/urn:li:[a-z0-9]+:[a-z0-9]+/i', $clean, $matches)) {
                return 'https://www.linkedin.com/embed/feed/update/' . $matches[0];
            }
        }

        // YouTube embed
        if (Str::contains($lower, 'youtu.be') || Str::contains($lower, 'youtube.com')) {
            $videoId = null;
            $path = parse_url($clean, PHP_URL_PATH) ?? '';

            if (Str::startsWith($lower, 'http://youtu.be') || Str::startsWith($lower, 'https://youtu.be')) {
                $videoId = trim($path, '/');
            }

            if (!$videoId && preg_match('#/(embed|shorts)/([a-zA-Z0-9_-]{6,})#', $path, $matches)) {
                $videoId = $matches[2] ?? null;
            }

            if (!$videoId) {
                $query = parse_url($clean, PHP_URL_QUERY) ?? '';
                parse_str($query, $params);
                foreach (['v', 'vi'] as $key) {
                    if (!empty($params[$key]) && is_string($params[$key])) {
                        $videoId = $params[$key];
                        break;
                    }
                }
            }

            if ($videoId && preg_match('/^[a-zA-Z0-9_-]{6,}$/', $videoId)) {
                $baseEmbed = sprintf('https://www.youtube.com/embed/%s', $videoId);
                $params = http_build_query([
                    'enablejsapi' => 1,
                    'playsinline' => 1,
                    'rel' => 0,
                    'modestbranding' => 1,
                    'mute' => 1,
                    'loop' => 1,
                    'playlist' => $videoId,
                ]);

                return $baseEmbed . '?' . $params;
            }
        }

        // Vimeo embed
        if (Str::contains($lower, 'vimeo.com')) {
            if (preg_match('#vimeo\\.com/(?:video/)?(\\d+)#i', $clean, $matches)) {
                return 'https://player.vimeo.com/video/' . $matches[1];
            }
        }

        // Google Drive preview
        if (Str::contains($lower, 'drive.google.com')) {
            if (preg_match('/\\/file\\/d\\/([a-zA-Z0-9_-]+)/', $clean, $matches)) {
                return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
            }

            $query = parse_url($clean, PHP_URL_QUERY) ?? '';
            parse_str($query, $params);
            if (!empty($params['id']) && is_string($params['id'])) {
                return 'https://drive.google.com/file/d/' . $params['id'] . '/preview';
            }
        }

        return null;
    }

    /**
     * Identify the provider for a given video URL.
     */
    public static function provider(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $clean = trim(Str::lower($url));
        if ($clean === '') {
            return null;
        }

        if (Str::contains($clean, ['youtube.com', 'youtu.be'])) {
            return 'youtube';
        }

        if (Str::contains($clean, 'vimeo.com')) {
            return 'vimeo';
        }

        if (Str::contains($clean, ['instagram.com', 'instagr.am'])) {
            return 'instagram';
        }

        if (Str::contains($clean, 'linkedin.com')) {
            return 'linkedin';
        }

        if (Str::contains($clean, 'drive.google.com')) {
            return 'drive';
        }

        return null;
    }

    private static function cleanUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $clean = trim($url);

        return $clean === '' ? null : $clean;
    }
}
