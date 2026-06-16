<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciProduct;
use App\Support\ThedolciCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(): View
    {
        $products = Schema::hasTable('thedolci_products')
            ? ThedolciProduct::query()->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('thedolci.admin.products.index', [
            'products' => $products,
            'dbReady' => Schema::hasTable('thedolci_products'),
        ]);
    }

    public function create(): View
    {
        return view('thedolci.admin.products.form', [
            'product' => new ThedolciProduct(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('thedolci_products')) {
            return back()->withErrors(['db' => 'Run migrations first to manage products.']);
        }

        $uploadErrorMessage = $this->resolveUploadErrorMessage('cover_image_file');
        if ($uploadErrorMessage !== null) {
            return back()
                ->withErrors(['cover_image_file' => $uploadErrorMessage])
                ->withInput();
        }

        $galleryUploadErrorMessage = $this->resolveUploadErrorMessage('gallery_image_files');
        if ($galleryUploadErrorMessage !== null) {
            return back()
                ->withErrors(['gallery_image_files' => $galleryUploadErrorMessage])
                ->withInput();
        }

        $data = $this->validatePayload($request);

        ThedolciProduct::query()->create($data);

        return redirect()->route('thedolci.admin.products.index')->with('success', 'Product created.');
    }

    public function edit(ThedolciProduct $product): View
    {
        return view('thedolci.admin.products.form', [
            'product' => $product,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, ThedolciProduct $product): RedirectResponse
    {
        if (! Schema::hasTable('thedolci_products')) {
            return back()->withErrors(['db' => 'Run migrations first to manage products.']);
        }

        $uploadErrorMessage = $this->resolveUploadErrorMessage('cover_image_file');
        if ($uploadErrorMessage !== null) {
            return back()
                ->withErrors(['cover_image_file' => $uploadErrorMessage])
                ->withInput();
        }

        $galleryUploadErrorMessage = $this->resolveUploadErrorMessage('gallery_image_files');
        if ($galleryUploadErrorMessage !== null) {
            return back()
                ->withErrors(['gallery_image_files' => $galleryUploadErrorMessage])
                ->withInput();
        }

        $data = $this->validatePayload($request, $product);

        $product->update($data);

        return redirect()->route('thedolci.admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(ThedolciProduct $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('thedolci.admin.products.index')->with('success', 'Product deleted.');
    }

    public function seedDefaults(): RedirectResponse
    {
        ThedolciCatalog::bootstrapDefaultsInDatabase();

        return redirect()->route('thedolci.admin.products.index')->with('success', 'Default catalog imported.');
    }

    private function validatePayload(Request $request, ?ThedolciProduct $product = null): array
    {
        $validated = $request->validate([
            'slug' => [
                'required',
                'string',
                'max:160',
                'alpha_dash',
                Rule::unique('thedolci_products', 'slug')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:160'],
            'headline' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'story' => ['nullable', 'string'],
            'cover_image' => [
                'nullable',
                'string',
                'max:500',
                'required_without:cover_image_file',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalizedValue = trim((string) $value);

                    if ($normalizedValue === '') {
                        return;
                    }

                    if ($this->isValidImageReference($normalizedValue)) {
                        return;
                    }

                    $fail('Cover image must be a valid URL or a local path starting with /storage/ or /image/.');
                },
            ],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:25600'],
            'gallery_images' => ['nullable', 'string'],
            'gallery_image_files' => ['nullable', 'array'],
            'gallery_image_files.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:25600'],
            'size_prices_input' => ['required', 'string', 'max:2500'],
            'pepper_price' => ['nullable', 'numeric', 'min:0'],
            'packaging_names' => ['nullable', 'array', 'max:60'],
            'packaging_names.*' => ['nullable', 'string', 'max:120'],
            'packaging_prices' => ['nullable', 'array', 'max:60'],
            'packaging_prices.*' => ['nullable', 'string', 'max:30'],
            'packaging_options_input' => ['nullable', 'string', 'max:2500'],
            'is_best_seller' => ['nullable', 'boolean'],
            'is_seasonal' => ['nullable', 'boolean'],
            'show_limited_edition' => ['nullable', 'boolean'],
            'seasonal_ends_at' => ['nullable', 'date'],
            'limited_quantity' => ['nullable', 'integer', 'min:1'],
            'preorder_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $galleryImages = collect(preg_split('/[\r\n,]+/', (string) ($validated['gallery_images'] ?? '')))
            ->map(fn ($item) => $this->normalizeImageReference((string) $item))
            ->filter()
            ->values()
            ->all();

        $sizePrices = $this->parseSizePrices((string) $validated['size_prices_input']);
        $packagingOptions = $this->parsePackagingOptionsFromFields(
            (array) ($validated['packaging_names'] ?? []),
            (array) ($validated['packaging_prices'] ?? [])
        );

        // Keep backward compatibility for legacy submissions using Name|Price text input.
        if (empty($packagingOptions) && filled($validated['packaging_options_input'] ?? null)) {
            $packagingOptions = $this->parsePackagingOptions($validated['packaging_options_input'] ?? null);
        }

        $coverImage = $this->normalizeImageReference($validated['cover_image'] ?? null);

        if ($request->hasFile('cover_image_file')) {
            if ($product) {
                $this->deleteStoredProductImage($product->cover_image);
            }

            $imagePath = $request->file('cover_image_file')->store('thedolci/products', 'public');
            $coverImage = $this->normalizeImageReference(Storage::disk('public')->url($imagePath));
        }

        if ($request->hasFile('gallery_image_files')) {
            $uploadedGalleryFiles = $request->file('gallery_image_files', []);

            if (! is_array($uploadedGalleryFiles)) {
                $uploadedGalleryFiles = [$uploadedGalleryFiles];
            }

            foreach ($uploadedGalleryFiles as $uploadedGalleryFile) {
                if (! $uploadedGalleryFile) {
                    continue;
                }

                $galleryImagePath = $uploadedGalleryFile->store('thedolci/products', 'public');
                $galleryImages[] = $this->normalizeImageReference(Storage::disk('public')->url($galleryImagePath));
            }
        }

        $galleryImages = collect($galleryImages)
            ->filter()
            ->reject(fn (string $image) => $coverImage !== '' && $image === $coverImage)
            ->unique()
            ->values()
            ->all();

        $payload = [
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'headline' => $validated['headline'] ?? null,
            'description' => $validated['description'] ?? null,
            'story' => $validated['story'] ?? null,
            'cover_image' => $coverImage,
            'gallery_images' => $galleryImages,
            'size_prices' => $sizePrices,
            'pepper_price' => round((float) ($validated['pepper_price'] ?? 0), 2),
            'packaging_options' => $packagingOptions,
            'is_best_seller' => (bool) ($validated['is_best_seller'] ?? false),
            'is_seasonal' => (bool) ($validated['is_seasonal'] ?? false),
            'seasonal_ends_at' => $validated['seasonal_ends_at'] ?? null,
            'limited_quantity' => $validated['limited_quantity'] ?? null,
            'preorder_enabled' => (bool) ($validated['preorder_enabled'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];

        if (Schema::hasColumn('thedolci_products', 'show_limited_edition')) {
            $payload['show_limited_edition'] = (bool) ($validated['show_limited_edition'] ?? false);
        }

        return $payload;
    }

    private function resolveUploadErrorMessage(string $fieldName): ?string
    {
        if (! isset($_FILES[$fieldName]['error'])) {
            return null;
        }

        $rawErrorCode = $_FILES[$fieldName]['error'];
        $errorCodes = is_array($rawErrorCode) ? $rawErrorCode : [$rawErrorCode];

        foreach ($errorCodes as $errorCode) {
            $message = $this->uploadErrorMessageForCode((int) $errorCode);

            if ($message !== null) {
                return $message;
            }
        }

        return null;
    }

    private function uploadErrorMessageForCode(int $errorCode): ?string
    {
        if ($errorCode === UPLOAD_ERR_OK || $errorCode === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
            return 'The selected image exceeds the upload size limit. Please upload an image smaller than 25 MB.';
        }

        if ($errorCode === UPLOAD_ERR_PARTIAL) {
            return 'The image upload was interrupted. Please try again.';
        }

        if ($errorCode === UPLOAD_ERR_NO_TMP_DIR) {
            return 'Image upload failed because the temporary upload directory is missing on the server.';
        }

        if ($errorCode === UPLOAD_ERR_CANT_WRITE) {
            return 'Image upload failed because the server cannot write the file.';
        }

        if ($errorCode === UPLOAD_ERR_EXTENSION) {
            return 'Image upload was stopped by a server extension.';
        }

        return 'Image upload failed. Please try again.';
    }

    private function isValidImageReference(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }

        $normalized = ltrim(str_replace('\\', '/', $value), '/');

        return Str::startsWith($normalized, ['storage/', 'image/']);
    }

    private function normalizeImageReference(?string $value): ?string
    {
        $cleanedValue = trim((string) $value);

        if ($cleanedValue === '') {
            return null;
        }

        $cleanedValue = str_replace('\\', '/', $cleanedValue);

        if (Str::startsWith($cleanedValue, ['storage/', 'image/'])) {
            return '/' . ltrim($cleanedValue, '/');
        }

        if (Str::startsWith($cleanedValue, ['/storage/', '/image/'])) {
            return $cleanedValue;
        }

        $parsedUrl = parse_url($cleanedValue);

        if (! is_array($parsedUrl)) {
            return $cleanedValue;
        }

        $host = strtolower((string) ($parsedUrl['host'] ?? ''));
        $path = (string) ($parsedUrl['path'] ?? '');
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && Str::startsWith($path, ['/storage/', '/image/'])) {
            return $path . $query . $fragment;
        }

        return $cleanedValue;
    }

    private function deleteStoredProductImage(?string $imageReference): void
    {
        $normalizedReference = $this->normalizeImageReference($imageReference);

        if ($normalizedReference === null) {
            return;
        }

        $normalizedPath = ltrim($normalizedReference, '/');

        if (! Str::startsWith($normalizedPath, 'storage/')) {
            return;
        }

        $storagePath = Str::after($normalizedPath, 'storage/');

        if ($storagePath !== '' && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    /**
     * @return array<string, float>
     */
    private function parseSizePrices(string $input): array
    {
        $sizePrices = collect(preg_split('/\r\n|\r|\n/', $input))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->reduce(function (array $carry, string $line) {
                [$size, $price] = array_map('trim', array_pad(explode('|', $line, 2), 2, ''));

                if ($size === '' || $price === '') {
                    return $carry;
                }

                if (! is_numeric($price) || (float) $price < 0) {
                    throw ValidationException::withMessages([
                        'size_prices_input' => "Invalid price in line: {$line}",
                    ]);
                }

                $carry[$size] = round((float) $price, 2);

                return $carry;
            }, []);

        if (empty($sizePrices)) {
            throw ValidationException::withMessages([
                'size_prices_input' => 'Add at least one size in this format: Size|Price',
            ]);
        }

        return $sizePrices;
    }

    /**
     * @return array<int, array{name: string, price: float}>
     */
    private function parsePackagingOptionsFromFields(array $names, array $prices): array
    {
        $rowCount = max(count($names), count($prices));

        if ($rowCount === 0) {
            return [];
        }

        $options = [];

        for ($index = 0; $index < $rowCount; $index++) {
            $name = trim((string) ($names[$index] ?? ''));
            $rawPrice = trim((string) ($prices[$index] ?? ''));

            if ($name === '' && $rawPrice === '') {
                continue;
            }

            if ($name === '') {
                throw ValidationException::withMessages([
                    "packaging_names.{$index}" => 'Packaging option name is required when price is provided.',
                ]);
            }

            if ($rawPrice === '') {
                $rawPrice = '0';
            }

            $normalizedPrice = str_replace(',', '.', $rawPrice);

            if (! is_numeric($normalizedPrice) || (float) $normalizedPrice < 0) {
                throw ValidationException::withMessages([
                    "packaging_prices.{$index}" => "Invalid packaging price for option: {$name}",
                ]);
            }

            $options[$name] = [
                'name' => $name,
                'price' => round((float) $normalizedPrice, 2),
            ];
        }

        return array_values($options);
    }

    /**
     * @return array<int, array{name: string, price: float}>
     */
    private function parsePackagingOptions(?string $input): array
    {
        $rows = collect(preg_split('/\r\n|\r|\n/', (string) $input))
            ->map(fn ($line) => trim((string) $line))
            ->filter();

        if ($rows->isEmpty()) {
            return [];
        }

        $options = $rows
            ->reduce(function (array $carry, string $line) {
                if (str_contains($line, '|')) {
                    [$name, $price] = array_map('trim', array_pad(explode('|', $line, 2), 2, '0'));
                } else {
                    $name = trim($line);
                    $price = '0';

                    $extracted = $this->extractTrailingPackagingPrice($line);
                    if ($extracted !== null) {
                        $name = $extracted['name'];
                        $price = $extracted['price'];
                    }
                }

                if ($name === '') {
                    return $carry;
                }

                if ($price === '') {
                    $price = '0';
                }

                $price = str_replace(',', '.', $price);

                if (! is_numeric($price) || (float) $price < 0) {
                    throw ValidationException::withMessages([
                        'packaging_options_input' => "Invalid packaging price in line: {$line}",
                    ]);
                }

                $carry[$name] = [
                    'name' => $name,
                    'price' => round((float) $price, 2),
                ];

                return $carry;
            }, []);

        return array_values($options);
    }

    /**
     * Accepts legacy lines like "Premium Box3.00" and extracts trailing decimal price.
     *
     * @return array{name: string, price: string}|null
     */
    private function extractTrailingPackagingPrice(string $value): ?array
    {
        $cleaned = trim($value);

        if ($cleaned === '') {
            return null;
        }

        if (! preg_match('/^(?<name>.+?)\s*(?:\(?\s*\+?\s*(?:JOD|JD|\$)?\s*)?(?<price>\d+[.,]\d{1,2})\s*\)?$/iu', $cleaned, $matches)) {
            return null;
        }

        $name = trim((string) ($matches['name'] ?? ''));
        $price = trim((string) ($matches['price'] ?? ''));

        if ($name === '' || $price === '') {
            return null;
        }

        return [
            'name' => $name,
            'price' => str_replace(',', '.', $price),
        ];
    }
}
