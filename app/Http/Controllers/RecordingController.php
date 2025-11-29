<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use App\Services\UserGoogleDriveService;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class RecordingController extends Controller
{
    public function listDrive(Request $request, UserGoogleDriveService $driveService): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $limit = (int) $request->integer('limit', 20);
        $files = $driveService->listUserVideos($user, $limit);

        $payload = collect($files)
            ->filter(fn ($file) => $file instanceof DriveFile)
            ->map(fn (DriveFile $file) => $this->normalizeDriveFile($file))
            ->values();

        return response()->json([
            'data' => $payload,
        ]);
    }

    public function storeFromDrive(Request $request, UserGoogleDriveService $driveService): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $validated = $request->validate([
            'file_id' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'workshop_id' => ['nullable', Rule::exists('workshops', 'id')],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $fileInfo = $driveService->getFileInfo($user, $validated['file_id']);

        if (! $fileInfo) {
            return response()->json([
                'message' => 'تعذر جلب بيانات الملف أو ليس لديك صلاحية الوصول.',
            ], 422);
        }

        if (! empty($validated['title'])) {
            $fileInfo['title'] = $validated['title'];
        }

        $recording = $driveService->createRecordingEntry(
            $user,
            $fileInfo,
            (bool) ($validated['is_public'] ?? false),
            $validated['workshop_id'] ?? null
        );

        return response()->json([
            'message' => 'تم حفظ التسجيل بنجاح.',
            'recording' => $recording->fresh('workshop'),
        ], 201);
    }

    protected function normalizeDriveFile(DriveFile $file): array
    {
        $fileId = $file->getId();
        $previewUrl = $fileId
            ? sprintf('https://drive.google.com/file/d/%s/preview', $fileId)
            : null;

        return [
            'id' => $fileId,
            'file_id' => $fileId,
            'title' => $file->getName(),
            'mime_type' => $file->getMimeType(),
            'preview_url' => $previewUrl,
            'watch_url' => $file->getWebViewLink() ?: $previewUrl ?: $file->getWebContentLink(),
            'modified_at' => $file->getModifiedTime(),
            'owners' => collect($file->getOwners() ?: [])
                ->map(fn ($owner) => [
                    'email' => $owner->getEmailAddress(),
                    'name' => $owner->getDisplayName(),
                ])
                ->values()
                ->all(),
        ];
    }
}
