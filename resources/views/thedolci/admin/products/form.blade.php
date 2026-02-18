@extends('thedolci.layouts.admin')

@section('title', ($isEdit ? 'Edit' : 'Create') . ' Product | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <h1>{{ $isEdit ? 'Edit Product' : 'Create Product' }}</h1>
        @php
            $sizeLinesDefault = collect($product->size_prices ?? [])
                ->map(fn ($price, $size) => trim((string) $size) . '|' . number_format((float) $price, 2, '.', ''))
                ->implode(PHP_EOL);

            if ($sizeLinesDefault === '') {
                $sizeLinesDefault = implode(PHP_EOL, ['Small|0', 'Medium|0']);
            }

            $packagingRowsDefault = collect($product->packaging_options ?? [])
                ->map(function ($option) {
                    if (is_array($option)) {
                        $name = trim((string) ($option['name'] ?? ''));
                        $price = (float) ($option['price'] ?? 0);

                        return $name !== ''
                            ? ['name' => $name, 'price' => number_format($price, 2, '.', '')]
                            : null;
                    }

                    return null;
                })
                ->filter()
                ->values();

            $oldPackagingNames = old('packaging_names');
            $oldPackagingPrices = old('packaging_prices');

            if (is_array($oldPackagingNames) || is_array($oldPackagingPrices)) {
                $oldPackagingNames = is_array($oldPackagingNames) ? array_values($oldPackagingNames) : [];
                $oldPackagingPrices = is_array($oldPackagingPrices) ? array_values($oldPackagingPrices) : [];
                $rowCount = max(count($oldPackagingNames), count($oldPackagingPrices));
                $packagingRowsDefault = collect();

                for ($index = 0; $index < $rowCount; $index++) {
                    $packagingRowsDefault->push([
                        'name' => trim((string) ($oldPackagingNames[$index] ?? '')),
                        'price' => trim((string) ($oldPackagingPrices[$index] ?? '')),
                    ]);
                }
            }

            if ($packagingRowsDefault->isEmpty()) {
                $packagingRowsDefault = collect([['name' => '', 'price' => '']]);
            }

            $galleryImagesDefault = is_array($product->gallery_images ?? null) ? $product->gallery_images : [];
        @endphp

        <form method="POST" action="{{ $isEdit ? route('thedolci.admin.products.update', $product) : route('thedolci.admin.products.store') }}" class="dolci-form-card" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="dolci-form-grid-2">
                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                </div>
                <div>
                    <label>Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" required>
                </div>
            </div>

            <label>Headline</label>
            <input type="text" name="headline" value="{{ old('headline', $product->headline) }}">

            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>

            <label>Story</label>
            <textarea name="story" rows="4">{{ old('story', $product->story) }}</textarea>

            <label>Cover Image URL or Local Path</label>
            <input type="text" name="cover_image" value="{{ old('cover_image', $product->cover_image) }}" placeholder="https://example.com/image.jpg or /storage/thedolci/products/cover.jpg">
            <p class="dolci-limited">Use a full URL, or a local path like /storage/... or /image/...</p>

            <label>Cover Image File (Local Upload)</label>
            <input type="file" name="cover_image_file" accept=".jpg,.jpeg,.png,.webp,.avif,image/*">

            @if(!empty($product->cover_image))
                <p class="dolci-limited">Current cover image preview:</p>
                <img src="{{ $product->cover_image }}" alt="{{ $product->name ?: 'Product cover image' }}" class="dolci-rounded-img" loading="lazy">
            @endif

            <label>Gallery Images</label>
            <textarea name="gallery_images" rows="4">{{ old('gallery_images', implode(PHP_EOL, $galleryImagesDefault)) }}</textarea>

            <label>Gallery Image Files (Multiple Upload)</label>
            <input type="file" name="gallery_image_files[]" accept=".jpg,.jpeg,.png,.webp,.avif,image/*" multiple>
            <p class="dolci-limited">You can select multiple images at once. They will be added to gallery images.</p>

            @if(!empty($galleryImagesDefault))
                <p class="dolci-limited">Current gallery images:</p>
                <div class="dolci-thumb-grid">
                    @foreach($galleryImagesDefault as $galleryImage)
                        @continue(empty($galleryImage))
                        <img src="{{ $galleryImage }}" alt="{{ $product->name ?: 'Product gallery image' }}" class="dolci-rounded-img" loading="lazy">
                    @endforeach
                </div>
            @endif

            <label>Sizes & Prices (one per line: Size|Price)</label>
            <textarea name="size_prices_input" rows="5" required>{{ old('size_prices_input', $sizeLinesDefault) }}</textarea>

            <label>Packaging Options</label>
            <p class="dolci-limited">Add packaging item and price in separate fields.</p>
            <div data-packaging-rows>
                @foreach($packagingRowsDefault as $row)
                    <div class="dolci-packaging-row" data-packaging-row>
                        <div>
                            <label>Packaging Item</label>
                            <input type="text" name="packaging_names[]" value="{{ $row['name'] ?? '' }}" placeholder="Gift Box">
                        </div>
                        <div class="dolci-packaging-price">
                            <label>Price (JOD)</label>
                            <input type="number" name="packaging_prices[]" value="{{ $row['price'] ?? '' }}" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <button type="button" class="dolci-btn dolci-btn-link dolci-packaging-remove" data-packaging-remove>Remove</button>
                    </div>
                @endforeach
            </div>

            <template id="packaging-row-template">
                <div class="dolci-packaging-row" data-packaging-row>
                    <div>
                        <label>Packaging Item</label>
                        <input type="text" name="packaging_names[]" value="" placeholder="Gift Box">
                    </div>
                    <div class="dolci-packaging-price">
                        <label>Price (JOD)</label>
                        <input type="number" name="packaging_prices[]" value="" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <button type="button" class="dolci-btn dolci-btn-link dolci-packaging-remove" data-packaging-remove>Remove</button>
                </div>
            </template>

            <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-packaging-add>Add packaging option</button>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Seasonal End Date</label>
                    <input type="datetime-local" name="seasonal_ends_at" value="{{ old('seasonal_ends_at', optional($product->seasonal_ends_at)->format('Y-m-d\\TH:i')) }}">
                </div>
                <div>
                    <label>Limited Quantity</label>
                    <input type="number" min="1" name="limited_quantity" value="{{ old('limited_quantity', $product->limited_quantity) }}">
                </div>
            </div>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
                </div>
                <div class="dolci-check-stack">
                    <label><input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller', $product->is_best_seller) ? 'checked' : '' }}> Best Seller</label>
                    <label><input type="checkbox" name="is_seasonal" value="1" {{ old('is_seasonal', $product->is_seasonal) ? 'checked' : '' }}> Seasonal</label>
                    <label><input type="checkbox" name="show_limited_edition" value="1" {{ old('show_limited_edition', $product->show_limited_edition) ? 'checked' : '' }}> Show as Limited Edition</label>
                    <label><input type="checkbox" name="preorder_enabled" value="1" {{ old('preorder_enabled', $product->preorder_enabled) ? 'checked' : '' }}> Pre-order Enabled</label>
                    <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}> Active</label>
                </div>
            </div>

            <button type="submit" class="dolci-btn dolci-btn-primary">{{ $isEdit ? 'Update Product' : 'Create Product' }}</button>
        </form>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rowsContainer = document.querySelector('[data-packaging-rows]');
    const addButton = document.querySelector('[data-packaging-add]');
    const rowTemplate = document.getElementById('packaging-row-template');

    if (!rowsContainer || !addButton || !rowTemplate) {
        return;
    }

    const clearRowInputs = (row) => {
        const nameInput = row.querySelector('input[name="packaging_names[]"]');
        const priceInput = row.querySelector('input[name="packaging_prices[]"]');

        if (nameInput) {
            nameInput.value = '';
        }

        if (priceInput) {
            priceInput.value = '';
        }
    };

    const bindRemoveButton = (row) => {
        const removeButton = row.querySelector('[data-packaging-remove]');

        if (!removeButton) {
            return;
        }

        removeButton.addEventListener('click', () => {
            const rows = rowsContainer.querySelectorAll('[data-packaging-row]');

            if (rows.length <= 1) {
                clearRowInputs(row);
                return;
            }

            row.remove();
        });
    };

    rowsContainer.querySelectorAll('[data-packaging-row]').forEach((row) => {
        bindRemoveButton(row);
    });

    addButton.addEventListener('click', () => {
        const fragment = rowTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-packaging-row]');

        if (!row) {
            return;
        }

        bindRemoveButton(row);
        rowsContainer.appendChild(fragment);
    });
});
</script>
@endsection

