<?php

namespace App\Support;

class Currency
{
    public const DEFAULT_DECIMALS = 2;

    public static function default(): string
    {
        return strtoupper(config('finance.default_currency', 'JOD'));
    }

    public static function all(): array
    {
        $configured = config('finance.supported_currencies', []);

        if (empty($configured)) {
            return [
                'JOD' => [
                    'label' => 'Jordanian Dinar',
                    'symbol' => 'JOD',
                    'decimals' => self::DEFAULT_DECIMALS,
                    'rate_to_usd' => 1.41,
                ],
            ];
        }

        return collect($configured)
            ->mapWithKeys(function ($meta, $code) {
                $normalizedCode = strtoupper($code);

                return [
                    $normalizedCode => array_merge([
                        'label' => $normalizedCode,
                        'symbol' => $normalizedCode,
                        'decimals' => self::DEFAULT_DECIMALS,
                        'rate_to_usd' => 1.0,
                    ], $meta, [
                        'code' => $normalizedCode,
                    ]),
                ];
            })
            ->all();
    }

    public static function codes(): array
    {
        return array_keys(static::all());
    }

    public static function labels(): array
    {
        return collect(static::all())
            ->map(fn ($meta) => $meta['label'] ?? ($meta['code'] ?? ''))
            ->all();
    }

    public static function meta(?string $code): array
    {
        $code = strtoupper($code ?: static::default());

        return static::all()[$code] ?? [
            'code' => $code,
            'label' => $code,
            'symbol' => $code,
            'decimals' => self::DEFAULT_DECIMALS,
            'rate_to_usd' => 1.0,
        ];
    }

    public static function rateToUsd(?string $code): float
    {
        $meta = static::meta($code);

        return (float) ($meta['rate_to_usd'] ?? 1.0);
    }

    public static function convert(float $amount, string $from, ?string $to = null): float
    {
        $to = $to ? strtoupper($to) : static::default();
        $from = strtoupper($from);

        if ($from === $to) {
            return static::round($amount, $to);
        }

        $usdAmount = $amount * static::rateToUsd($from);
        $targetRate = static::rateToUsd($to) ?: 1.0;
        $converted = $targetRate > 0 ? $usdAmount / $targetRate : $usdAmount;

        return static::round($converted, $to);
    }

    public static function round(float $amount, ?string $code = null): float
    {
        $meta = static::meta($code);
        $decimals = (int) ($meta['decimals'] ?? self::DEFAULT_DECIMALS);

        return round($amount, $decimals);
    }

    public static function format(float $amount, ?string $code = null, bool $withSymbol = false): string
    {
        $meta = static::meta($code);
        $decimals = (int) ($meta['decimals'] ?? self::DEFAULT_DECIMALS);
        $formatted = number_format($amount, $decimals);
        $symbol = $withSymbol ? ($meta['symbol'] ?? $meta['code']) : ($meta['code'] ?? '');

        return trim($withSymbol ? "{$symbol} {$formatted}" : "{$formatted} {$meta['code']}");
    }
}
