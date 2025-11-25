@props([
    'base',
    'widths' => [],
    'formats' => ['avif', 'webp'],
    'fallback' => 'png',
    'lazy' => true,
    'alt' => '',
    'pictureClass' => '',
    'sizes' => null,
    'intrinsicWidth' => null,
    'intrinsicHeight' => null,
])

@php
    $normalizedBase = ltrim($base, '/');
    $normalizedFormats = collect($formats)
        ->map(fn ($format) => strtolower(trim($format)))
        ->filter()
        ->unique()
        ->values();

    $normalizedWidths = collect($widths)
        ->map(fn ($width) => (int) $width)
        ->filter(fn ($width) => $width > 0)
        ->unique()
        ->sort()
        ->values();

    $loadingAttr = $attributes->get('loading', $lazy ? 'lazy' : 'eager');

    $defaultImgAttributes = [
        'loading' => $loadingAttr,
        'decoding' => 'async',
    ];

    if ($sizes && ! $attributes->has('sizes')) {
        $defaultImgAttributes['sizes'] = $sizes;
    }

    $imgAttributes = $attributes->merge($defaultImgAttributes);

    $sourceTags = $normalizedFormats->map(function ($format) use ($normalizedBase, $normalizedWidths, $sizes) {
        $srcsetEntries = $normalizedWidths->map(function ($width) use ($normalizedBase, $format) {
            $relativePath = "{$normalizedBase}-{$width}.{$format}";
            if (! file_exists(public_path($relativePath))) {
                return null;
            }

            return asset($relativePath) . " {$width}w";
        })->filter();

        if ($srcsetEntries->isEmpty()) {
            $relativePath = "{$normalizedBase}.{$format}";
            if (! file_exists(public_path($relativePath))) {
                return null;
            }

            $srcsetEntries->push(asset($relativePath));
        }

        $attributes = [
            'type' => "image/{$format}",
            'srcset' => $srcsetEntries->implode(', '),
        ];

        if ($sizes) {
            $attributes['sizes'] = $sizes;
        }

        return '<source ' . collect($attributes)->map(fn ($value, $key) => "{$key}=\"{$value}\"")->implode(' ') . '>';
    })->filter();

    $fallbackRelativePath = "{$normalizedBase}.{$fallback}";
    $fallbackSrc = file_exists(public_path($fallbackRelativePath))
        ? asset($fallbackRelativePath)
        : asset("{$normalizedBase}.{$normalizedFormats->last()}");

    $providedWidth = $attributes->get('width') ?? $intrinsicWidth;
    $providedHeight = $attributes->get('height') ?? $intrinsicHeight;

    if (! $providedWidth || ! $providedHeight) {
        $dimensionCandidates = collect([$fallbackRelativePath])
            ->merge(
                $normalizedFormats->map(fn ($format) => "{$normalizedBase}.{$format}")
            )
            ->unique()
            ->filter();

        foreach ($dimensionCandidates as $dimensionCandidate) {
            $absolutePath = public_path($dimensionCandidate);

            if (! file_exists($absolutePath)) {
                continue;
            }

            $imageSize = @getimagesize($absolutePath);

            if (! $imageSize) {
                continue;
            }

            [$providedWidth, $providedHeight] = $imageSize;
            break;
        }
    }

    $providedWidth = (int) ($providedWidth ?: 1);
    $providedHeight = (int) ($providedHeight ?: 1);

    if (! $attributes->has('width')) {
        $imgAttributes = $imgAttributes->merge(['width' => $providedWidth]);
    }

    if (! $attributes->has('height')) {
        $imgAttributes = $imgAttributes->merge(['height' => $providedHeight]);
    }
@endphp

<picture class="{{ $pictureClass }}">
    {!! $sourceTags->implode(PHP_EOL) !!}
    <img src="{{ $fallbackSrc }}" alt="{{ $alt }}" {{ $imgAttributes }}>
</picture>

