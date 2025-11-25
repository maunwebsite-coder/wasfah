<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge',
        'title',
        'description',
        'image_alt',
        'image_path',
        'desktop_image_path',
        'mobile_image_path',
        'features',
        'actions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'desktop_image_url',
        'mobile_image_url',
    ];

    /**
     * Scope: active slides.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: order slides by sort_order then id.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Desktop image accessor.
     */
    public function getDesktopImageUrlAttribute(): ?string
    {
        $path = $this->desktop_image_path ?? $this->image_path;

        return $this->resolveImageUrl($path);
    }

    /**
     * Mobile image accessor with desktop fallback.
     */
    public function getMobileImageUrlAttribute(): ?string
    {
        $path = $this->mobile_image_path ?? $this->image_path;

        return $this->resolveImageUrl($path) ?: $this->getDesktopImageUrlAttribute();
    }

    /**
     * Transform the model to the structure consumed by the hero slider.
     *
     * @param array<string, array<string, mixed>> $dynamicActions
     */
    public function toHeroArray(array $dynamicActions = []): array
    {
        $image = $this->desktop_image_url ?? asset('image/wterm.webp');

        return [
            'badge' => $this->badge,
            'title' => $this->title,
            'description' => $this->description,
            'features' => $this->normalizedFeatures(),
            'actions' => $this->resolvedActions($dynamicActions),
            'image' => $image,
            'mobile_image' => $this->mobile_image_url ?? $image,
            'image_alt' => $this->image_alt ?: $this->title,
        ];
    }

    /**
     * Ensure features are trimmed lists of strings.
     *
     * @return array<int, string>
     */
    protected function normalizedFeatures(): array
    {
        return collect($this->features ?? [])
            ->map(fn ($feature) => is_string($feature) ? trim($feature) : '')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize and resolve action payloads.
     *
     * @param array<string, array<string, mixed>> $dynamicActions
     * @return array<int, array<string, mixed>>
     */
    protected function resolvedActions(array $dynamicActions = []): array
    {
        return collect($this->actions ?? [])
            ->map(function ($action) use ($dynamicActions) {
                $behavior = $action['behavior'] ?? 'static';

                if ($behavior !== 'static') {
                    return $this->resolveDynamicAction($behavior, $action, $dynamicActions);
                }

                $label = $this->cleanString($action['label'] ?? '');
                $url = $this->resolveActionUrl($action['url'] ?? '');

                if ($label === '' || !$url) {
                    return null;
                }

                return [
                    'label' => $label,
                    'url' => $url,
                    'icon' => $this->cleanString($action['icon'] ?? null),
                    'type' => $this->normalizeActionType($action['type'] ?? null) ?? 'primary',
                    'open_in_new_tab' => (bool) ($action['open_in_new_tab'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Resolve a dynamic action (e.g., create workshop) with optional overrides.
     *
     * @param array<string, mixed> $action
     * @param array<string, array<string, mixed>> $dynamicActions
     */
    protected function resolveDynamicAction(string $behavior, array $action, array $dynamicActions): ?array
    {
        $preset = $dynamicActions[$behavior] ?? null;

        if (!$preset) {
            return null;
        }

        $overrides = [
            'label' => $this->cleanString($action['label'] ?? null),
            'icon' => $this->cleanString($action['icon'] ?? null),
            'type' => $this->normalizeActionType($action['type'] ?? null),
            'url' => $this->resolveActionUrl($action['url'] ?? ''),
            'open_in_new_tab' => array_key_exists('open_in_new_tab', $action)
                ? (bool) $action['open_in_new_tab']
                : ($preset['open_in_new_tab'] ?? false),
        ];

        $resolved = [
            'label' => $overrides['label'] ?: ($preset['label'] ?? null),
            'url' => $overrides['url'] ?? ($preset['url'] ?? null),
            'icon' => $overrides['icon'] ?? ($preset['icon'] ?? null),
            'type' => $overrides['type'] ?? ($preset['type'] ?? 'primary'),
            'open_in_new_tab' => $overrides['open_in_new_tab'],
        ];

        if (!$resolved['label'] || !$resolved['url']) {
            return null;
        }

        return $resolved;
    }

    /**
     * Resolve stored media paths to URLs.
     */
    protected function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            $localPath = $this->normalizeLocalAssetPath($path);

            if ($localPath !== null) {
                return $this->resolveLocalAssetUrl($localPath);
            }

            return $path;
        }

        return $this->resolveLocalAssetUrl($path);
    }

    /**
     * Convert legacy local URLs (localhost/127.0.0.1) to a path we can serve from the current host.
     */
    protected function normalizeLocalAssetPath(string $url): ?string
    {
        $parts = parse_url($url);

        if (!$parts) {
            return null;
        }

        $host = $parts['host'] ?? '';
        $localHosts = ['127.0.0.1', 'localhost', '::1'];

        if (!in_array($host, $localHosts, true)) {
            return null;
        }

        $path = $parts['path'] ?? '';

        return $path ? ltrim($path, '/') : null;
    }

    /**
     * Resolve a relative storage/public path to a usable URL, returning null when the asset is missing.
     */
    protected function resolveLocalAssetUrl(string $path): ?string
    {
        $normalized = ltrim($path, '/');

        $storagePath = Str::replaceFirst('storage/', '', $normalized);
        $storageFullPath = storage_path('app/public/' . $storagePath);

        if (file_exists($storageFullPath)) {
            return Storage::disk('public')->url($storagePath);
        }

        $publicFullPath = public_path($normalized);

        if (file_exists($publicFullPath)) {
            return asset($normalized);
        }

        return null;
    }

    /**
     * Normalize action types to the supported set.
     */
    protected function normalizeActionType(?string $type): ?string
    {
        $allowed = ['primary', 'secondary', 'accent', 'ghost'];

        return in_array($type, $allowed, true) ? $type : null;
    }

    /**
     * Trim and sanitize arbitrary strings.
     */
    protected function cleanString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Convert stored URLs/route references into absolute URLs.
     */
    protected function resolveActionUrl(?string $value): ?string
    {
        $value = $this->cleanString($value);

        if (!$value) {
            return null;
        }

        if (Str::startsWith($value, 'route:')) {
            $routeName = trim(Str::after($value, 'route:'));
            if ($routeName !== '' && Route::has($routeName)) {
                return route($routeName);
            }

            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', 'mailto:', 'tel:', 'whatsapp://'])) {
            return $value;
        }

        if (Str::startsWith($value, '#')) {
            return $value;
        }

        if (Str::contains($value, '://')) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return url($value);
        }

        return url('/' . ltrim($value, '/'));
    }
}

