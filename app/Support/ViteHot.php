<?php

namespace App\Support;

use Illuminate\Support\Arr;

class ViteHot
{
    /**
     * Decide whether the Vite dev server should be used.
     */
    public static function shouldUseHotReload(): bool
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            return false;
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return false;
        }

        $hotTarget = trim((string) @file_get_contents($hotFile));

        if ($hotTarget === '') {
            return false;
        }

        $info = parse_url($hotTarget);

        if ($info === false || empty($info['host'])) {
            return false;
        }

        $host = static::normalizeHost($info['host']);
        $port = (int) Arr::get($info, 'port', 5173);
        $timeout = (float) config('app.vite_hot_ping_timeout', 0.15);
        $protocol = (Arr::get($info, 'scheme') === 'https') ? 'ssl://' : '';

        try {
            $connection = @fsockopen($protocol . $host, $port, $errno, $errstr, $timeout);

            if (is_resource($connection)) {
                fclose($connection);

                return true;
            }
        } catch (\Throwable) {
            // Intentionally ignored – we just fall back to built assets.
        }

        return false;
    }

    /**
     * Determine whether built Vite assets are available.
     */
    public static function hasBuildManifest(string $buildDirectory = 'build'): bool
    {
        $buildDirectory = trim($buildDirectory, '/\\');

        return is_file(public_path($buildDirectory . '/manifest.json'));
    }

    /**
     * Determine whether a specific entry exists in the build manifest.
     */
    public static function hasBuildManifestEntry(string $entry, string $buildDirectory = 'build'): bool
    {
        $manifest = static::readBuildManifest($buildDirectory);

        if (! is_array($manifest)) {
            return false;
        }

        return array_key_exists($entry, $manifest);
    }

    /**
     * Determine whether all provided entries exist in the build manifest.
     *
     * @param  array<int, string>  $entries
     */
    public static function hasBuildManifestEntries(array $entries, string $buildDirectory = 'build'): bool
    {
        if ($entries === []) {
            return false;
        }

        $manifest = static::readBuildManifest($buildDirectory);

        if (! is_array($manifest)) {
            return false;
        }

        foreach ($entries as $entry) {
            if (! array_key_exists($entry, $manifest)) {
                return false;
            }
        }

        return true;
    }

    protected static function normalizeHost(string $host): string
    {
        $host = trim($host, '[]');

        return $host === '::1' ? '127.0.0.1' : $host;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function readBuildManifest(string $buildDirectory = 'build'): ?array
    {
        $buildDirectory = trim($buildDirectory, '/\\');
        $manifestPath = public_path($buildDirectory . '/manifest.json');

        if (! is_file($manifestPath)) {
            return null;
        }

        $decoded = json_decode((string) @file_get_contents($manifestPath), true);

        return is_array($decoded) ? $decoded : null;
    }
}
