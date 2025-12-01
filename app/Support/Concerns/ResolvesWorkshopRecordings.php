<?php

namespace App\Support\Concerns;

use App\Models\Workshop;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

trait ResolvesWorkshopRecordings
{
    /**
     * Determine the most reliable recording URL for the workshop.
     */
    protected function resolveRecordingUrl(Workshop $workshop): ?string
    {
        $candidates = [
            $workshop->recording_url ?? null,
            $workshop->recording_link ?? null,
            $workshop->recording ?? null,
            $workshop->video_url ?? null,
            $workshop->meeting_link ?? null,
        ];

        foreach ($candidates as $candidate) {
            $trimmed = is_string($candidate) ? trim($candidate) : null;

            if ($trimmed !== null && $trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    /**
     * Attempt to build an embeddable preview URL from a recording source.
     */
    protected function buildRecordingPreviewUrl(?string $url): ?string
    {
        $normalizedUrl = is_string($url) ? trim($url) : null;

        if (! $normalizedUrl) {
            return null;
        }

        if ($preview = $this->buildDrivePreviewUrl($normalizedUrl)) {
            return $preview;
        }

        if ($preview = $this->buildYoutubePreviewUrl($normalizedUrl)) {
            return $preview;
        }

        if ($preview = $this->buildVimeoPreviewUrl($normalizedUrl)) {
            return $preview;
        }

        if ($preview = $this->buildLoomPreviewUrl($normalizedUrl)) {
            return $preview;
        }

        return null;
    }

    /**
     * Convert a Google Drive link into an embeddable preview URL.
     */
    protected function buildDrivePreviewUrl(?string $url): ?string
    {
        $normalizedUrl = is_string($url) ? trim($url) : null;

        if (! $normalizedUrl) {
            return null;
        }

        if (! $this->isGoogleDriveUrl($normalizedUrl)) {
            return null;
        }

        $fileId = $this->extractDriveFileId($normalizedUrl);

        if (! $fileId) {
            return null;
        }

        return sprintf('https://drive.google.com/file/d/%s/preview', $fileId);
    }

    /**
     * Attempt to build an embeddable YouTube URL from different share formats.
     */
    protected function buildYoutubePreviewUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '') {
            return null;
        }

        $allowedHosts = [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
            'youtu.be',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        $videoId = null;

        if ($host === 'youtu.be') {
            $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

            if ($path !== '') {
                $videoId = $path;
            }
        } else {
            $path = parse_url($url, PHP_URL_PATH) ?? '';

            if (preg_match('#/(embed|shorts)/([a-zA-Z0-9_-]{6,})#', $path, $matches)) {
                $videoId = $matches[2];
            }

            if (! $videoId) {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $params);

                foreach (['v', 'vi'] as $key) {
                    if (! empty($params[$key]) && is_string($params[$key])) {
                        $videoId = $params[$key];
                        break;
                    }
                }
            }
        }

        if (! $videoId || preg_match('/^[a-zA-Z0-9_-]{6,}$/', $videoId) !== 1) {
            return null;
        }

        return sprintf('https://www.youtube.com/embed/%s', $videoId);
    }

    /**
     * Attempt to build an embeddable Vimeo URL.
     */
    protected function buildVimeoPreviewUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '' || ! str_contains($host, 'vimeo.com')) {
            return null;
        }

        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if ($path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path)));
        $videoId = null;

        foreach (array_reverse($segments) as $segment) {
            if (preg_match('/^[0-9]{6,}$/', $segment)) {
                $videoId = $segment;
                break;
            }
        }

        if (! $videoId) {
            return null;
        }

        return sprintf('https://player.vimeo.com/video/%s', $videoId);
    }

    /**
     * Attempt to build an embeddable Loom URL.
     */
    protected function buildLoomPreviewUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '' || ! str_contains($host, 'loom.com')) {
            return null;
        }

        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if ($path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path)));
        $videoId = null;

        foreach (array_reverse($segments) as $segment) {
            if (preg_match('/^[a-zA-Z0-9_-]{10,}$/', $segment)) {
                $videoId = $segment;
                break;
            }
        }

        if (! $videoId) {
            return null;
        }

        return sprintf('https://www.loom.com/embed/%s', $videoId);
    }

    /**
     * Determine whether the URL points to a Google Drive endpoint we can embed.
     */
    protected function isGoogleDriveUrl(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '') {
            return false;
        }

        $allowedHosts = [
            'drive.google.com',
            'docs.google.com',
            'drive.googleusercontent.com',
            'drive.usercontent.google.com',
            'lh3.googleusercontent.com',
        ];

        foreach ($allowedHosts as $allowedHost) {
            if ($host === $allowedHost || str_ends_with($host, '.'.$allowedHost)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Try to extract a Google Drive file ID from different URL patterns.
     */
    protected function extractDriveFileId(string $url): ?string
    {
        $patterns = [
            '#/file/d/([a-zA-Z0-9_-]{10,})#',
            '#/open\?id=([^&]+)#',
            '#/uc\?id=([^&]+)#',
            '#/thumbnail\?id=([^&]+)#',
            '#/d/([a-zA-Z0-9_-]{10,})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $params);

            foreach (['id', 'file', 'fileId'] as $key) {
                if (! empty($params[$key]) && is_string($params[$key])) {
                    return $params[$key];
                }
            }
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (is_string($path) && $path !== '') {
            $segments = array_filter(explode('/', trim($path, '/')));

            foreach ($segments as $segment) {
                if (preg_match('/^[a-zA-Z0-9_-]{15,}$/', $segment)) {
                    return $segment;
                }
            }
        }

        return null;
    }

    /**
     * Determine whether the URL points directly to a video asset.
     */
    protected function isDirectVideoUrl(?string $url): bool
    {
        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            $path = $url;
        }

        return (bool) preg_match('/\.(mp4|m4v|mov|webm|ogv|ogg)(\?.*)?$/i', $path);
    }

    /**
     * Format the workshop date for display.
     */
    protected function formatWorkshopDate(Workshop $workshop, string $format = 'd F Y • h:i A'): ?string
    {
        try {
            $startDate = $workshop->start_date;
        } catch (\Throwable $exception) {
            Log::warning('Failed to read workshop start date for formatting.', [
                'workshop_id' => $workshop->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $startDate instanceof Carbon) {
            return null;
        }

        try {
            return $startDate->translatedFormat($format);
        } catch (\Throwable $exception) {
            Log::warning('Failed to format workshop start date.', [
                'workshop_id' => $workshop->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return $startDate->format('Y-m-d H:i');
        }
    }
}
