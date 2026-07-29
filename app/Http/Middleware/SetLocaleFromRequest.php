<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', [config('app.locale')]);
        $defaultLocale = config('app.locale', 'en');

        $queryLocale = $request->query('lang');
        $sessionLocale = $request->session()->get('app_locale');
        $cookieLocale = $request->cookie('app_locale');

        $locale = $defaultLocale;

        if ($queryLocale && in_array($queryLocale, $availableLocales, true)) {
            $locale = $queryLocale;
            $request->session()->put('app_locale', $locale);
        } elseif ($sessionLocale && in_array($sessionLocale, $availableLocales, true)) {
            $locale = $sessionLocale;
        } elseif ($cookieLocale && in_array($cookieLocale, $availableLocales, true)) {
            $locale = $cookieLocale;
        }

        if (! in_array($locale, $availableLocales, true)) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if ($request->cookie('app_locale') !== $locale) {
            $response->headers->setCookie(cookie('app_locale', $locale, 60 * 24 * 30));
        }

        return $response;
    }
}
