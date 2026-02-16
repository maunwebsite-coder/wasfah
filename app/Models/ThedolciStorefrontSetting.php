<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ThedolciStorefrontSetting extends Model
{
    use HasFactory;

    protected $table = 'thedolci_storefront_settings';

    protected $fillable = [
        'hero_kicker',
        'hero_title',
        'hero_description',
        'hero_primary_button_text',
        'hero_secondary_button_text',
        'hero_metric_1_title',
        'hero_metric_1_subtitle',
        'hero_metric_2_title',
        'hero_metric_2_subtitle',
        'hero_metric_3_title',
        'hero_metric_3_subtitle',
        'hero_image_url',
        'hero_image_alt',
    ];

    /**
     * @return array<string, string>
     */
    public static function heroDefaults(): array
    {
        return [
            'kicker' => 'Cloud Kitchen - Fresh Daily',
            'title' => 'Authentic Italian Tiramisu, Made Fresh Daily',
            'description' => 'Elegant flavors, artisan quality, and premium presentation crafted for every occasion.',
            'primary_button_text' => 'Order Now',
            'secondary_button_text' => 'Seasonal Collection',
            'metric_1_title' => 'Fresh Daily',
            'metric_1_subtitle' => 'small-batch kitchen',
            'metric_2_title' => 'Premium Grade',
            'metric_2_subtitle' => 'imported ingredients',
            'metric_3_title' => 'Same-Day Slots',
            'metric_3_subtitle' => 'flexible delivery windows',
            'image_url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=1400&q=80',
            'image_alt' => 'Premium tiramisu',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heroContent(): array
    {
        $defaults = self::heroDefaults();

        if (! Schema::hasTable((new self())->getTable())) {
            return $defaults;
        }

        try {
            $settings = self::query()->first();
        } catch (QueryException) {
            return $defaults;
        }

        if (! $settings) {
            return $defaults;
        }

        $mapped = [
            'kicker' => $settings->hero_kicker,
            'title' => $settings->hero_title,
            'description' => $settings->hero_description,
            'primary_button_text' => $settings->hero_primary_button_text,
            'secondary_button_text' => $settings->hero_secondary_button_text,
            'metric_1_title' => $settings->hero_metric_1_title,
            'metric_1_subtitle' => $settings->hero_metric_1_subtitle,
            'metric_2_title' => $settings->hero_metric_2_title,
            'metric_2_subtitle' => $settings->hero_metric_2_subtitle,
            'metric_3_title' => $settings->hero_metric_3_title,
            'metric_3_subtitle' => $settings->hero_metric_3_subtitle,
            'image_url' => $settings->hero_image_url,
            'image_alt' => $settings->hero_image_alt,
        ];

        foreach ($mapped as $key => $value) {
            $cleanedValue = trim((string) $value);

            if ($cleanedValue !== '') {
                if ($key === 'image_url') {
                    $cleanedValue = self::normalizeHeroImageUrl($cleanedValue);
                }

                $defaults[$key] = $cleanedValue;
            }
        }

        return $defaults;
    }

    private static function normalizeHeroImageUrl(string $url): string
    {
        if (Str::startsWith($url, 'storage/')) {
            return '/' . ltrim($url, '/');
        }

        if (Str::startsWith($url, '/storage/')) {
            return $url;
        }

        $parsedUrl = parse_url($url);

        if (! is_array($parsedUrl)) {
            return $url;
        }

        $host = strtolower((string) ($parsedUrl['host'] ?? ''));
        $path = (string) ($parsedUrl['path'] ?? '');
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        if (in_array($host, ['127.0.0.1', 'localhost'], true) && Str::startsWith($path, '/storage/')) {
            return $path . $query . $fragment;
        }

        return $url;
    }
}
