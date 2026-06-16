<?php

namespace App\Providers;

use App\Helpers\Breadcrumbs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helperPath = app_path('Support/GoogleMeetAccountChooser.php');

        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            Route::get('/storage/{path}', function (string $path) {
                $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

                if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
                    abort(404);
                }

                if (! Storage::disk('public')->exists($normalizedPath)) {
                    abort(404);
                }

                return response()->file(Storage::disk('public')->path($normalizedPath));
            })->where('path', '.*');
        });

        View::composer('layouts.app', function ($view) {
            $view->with('breadcrumbs', Breadcrumbs::generate());
        });

        View::share('globalContentTranslations', config('content-translations.locales', []));
    }
}
