<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciStorefrontSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminStorefrontController extends Controller
{
    public function edit(): View
    {
        return view('thedolci.admin.storefront.edit', [
            'hero' => ThedolciStorefrontSetting::heroContent(),
            'dbReady' => Schema::hasTable('thedolci_storefront_settings'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('thedolci_storefront_settings')) {
            return back()->withErrors(['db' => 'Run migrations first to manage storefront content.']);
        }

        $uploadErrorMessage = $this->resolveUploadErrorMessage('hero_image_file');
        if ($uploadErrorMessage !== null) {
            return back()
                ->withErrors(['hero_image_file' => $uploadErrorMessage])
                ->withInput();
        }

        $validated = $request->validate([
            'hero_kicker' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'hero_primary_button_text' => ['nullable', 'string', 'max:80'],
            'hero_secondary_button_text' => ['nullable', 'string', 'max:80'],
            'hero_metric_1_title' => ['nullable', 'string', 'max:80'],
            'hero_metric_1_subtitle' => ['nullable', 'string', 'max:120'],
            'hero_metric_2_title' => ['nullable', 'string', 'max:80'],
            'hero_metric_2_subtitle' => ['nullable', 'string', 'max:120'],
            'hero_metric_3_title' => ['nullable', 'string', 'max:80'],
            'hero_metric_3_subtitle' => ['nullable', 'string', 'max:120'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:25600'],
            'hero_image_alt' => ['nullable', 'string', 'max:180'],
        ]);

        $settings = ThedolciStorefrontSetting::query()->firstOrNew(['id' => 1]);

        $payload = [
            'hero_kicker' => $this->emptyToNull($validated['hero_kicker'] ?? null),
            'hero_title' => $this->emptyToNull($validated['hero_title'] ?? null),
            'hero_description' => $this->emptyToNull($validated['hero_description'] ?? null),
            'hero_primary_button_text' => $this->emptyToNull($validated['hero_primary_button_text'] ?? null),
            'hero_secondary_button_text' => $this->emptyToNull($validated['hero_secondary_button_text'] ?? null),
            'hero_metric_1_title' => $this->emptyToNull($validated['hero_metric_1_title'] ?? null),
            'hero_metric_1_subtitle' => $this->emptyToNull($validated['hero_metric_1_subtitle'] ?? null),
            'hero_metric_2_title' => $this->emptyToNull($validated['hero_metric_2_title'] ?? null),
            'hero_metric_2_subtitle' => $this->emptyToNull($validated['hero_metric_2_subtitle'] ?? null),
            'hero_metric_3_title' => $this->emptyToNull($validated['hero_metric_3_title'] ?? null),
            'hero_metric_3_subtitle' => $this->emptyToNull($validated['hero_metric_3_subtitle'] ?? null),
            // Keep the currently saved image unless a new file is uploaded.
            'hero_image_url' => $this->emptyToNull($settings->hero_image_url),
            'hero_image_alt' => $this->emptyToNull($validated['hero_image_alt'] ?? null),
        ];

        if ($request->hasFile('hero_image_file')) {
            $imageUrl = $this->storeHeroImage($request->file('hero_image_file'));

            if ($imageUrl === null) {
                return back()
                    ->withErrors(['hero_image_file' => 'Unable to save the uploaded image. Please try again.'])
                    ->withInput();
            }

            $payload['hero_image_url'] = $imageUrl;
        }

        $settings->fill($payload);
        $settings->save();

        return redirect()
            ->route('thedolci.admin.storefront.edit')
            ->with('success', 'Storefront hero updated.');
    }

    private function emptyToNull(?string $value): ?string
    {
        $cleanedValue = trim((string) $value);

        return $cleanedValue === '' ? null : $cleanedValue;
    }

    private function resolveUploadErrorMessage(string $fieldName): ?string
    {
        $errorCode = (int) ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE);

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

    private function storeHeroImage(UploadedFile $file): ?string
    {
        if ($this->publicStorageIsWebAccessible()) {
            $storedPath = $file->store('thedolci/hero', 'public');

            if (is_string($storedPath) && $storedPath !== '') {
                return Storage::disk('public')->url($storedPath);
            }

            return null;
        }

        $targetDirectory = public_path('image/thedolci/hero');

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
            return null;
        }

        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        $filename = 'hero-' . now()->format('YmdHis') . '-' . Str::random(10) . '.' . $extension;

        $file->move($targetDirectory, $filename);

        return '/image/thedolci/hero/' . $filename;
    }

    private function publicStorageIsWebAccessible(): bool
    {
        $publicStoragePath = public_path('storage');

        return is_link($publicStoragePath) || is_dir($publicStoragePath);
    }
}
