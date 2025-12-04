<?php

namespace App\Support;

use Illuminate\Http\Request;

class IntendedUrl
{
    /**
     * Store a safe return URL in the session so auth flows can send the
     * user back to where they started.
     */
    public static function rememberFromRequest(Request $request): void
    {
        $hasExplicitReturn = $request->filled('return_to');

        if (! $hasExplicitReturn && $request->session()->has('url.intended')) {
            return;
        }

        $returnTo = self::extractSafeReturnUrl($request);

        if ($returnTo) {
            $request->session()->put('url.intended', $returnTo);
            $request->session()->put('auth.return_to', $returnTo);
        }
    }

    /**
     * Accept a return URL from query string or referrer, ensure it's for the
     * current host, and avoid login/register loops.
     */
    private static function extractSafeReturnUrl(Request $request): ?string
    {
        $candidate = $request->input('return_to') ?: $request->headers->get('referer');

        if (! $candidate || ! is_string($candidate)) {
            return null;
        }

        $parsed = parse_url($candidate);
        $path = $parsed['path'] ?? null;

        if (! $path && str_starts_with($candidate, '/')) {
            $path = $candidate;
        }

        if (! $path) {
            return null;
        }

        $sanitizedPath = '/' . ltrim($path, '/');

        if (in_array($sanitizedPath, ['/login', '/register', '/logout'], true)) {
            return null;
        }

        $host = $parsed['host'] ?? null;
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? $request->getHost();

        if ($host && $host !== $appHost) {
            return null;
        }

        $query = isset($parsed['query']) && $parsed['query'] !== ''
            ? '?' . $parsed['query']
            : '';

        return $sanitizedPath . $query;
    }
}
