<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workshop;
use App\Services\GoogleMeetService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Support\GoogleMeetAccountChooser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeetingRedirectController extends Controller
{
    public function __construct(
        protected GoogleMeetService $googleMeetService,
        protected WorkshopMeetingAttendeeSyncService $meetingAttendeeSyncService,
    ) {
    }

    public function redirect(Request $request): RedirectResponse
    {
        $rawToken = $request->query('token');

        if (!is_string($rawToken) || trim($rawToken) === '') {
            abort(404);
        }

        try {
            $payload = decrypt($rawToken);
        } catch (\Throwable $exception) {
            Log::warning('Invalid host redirect token.', ['error' => $exception->getMessage()]);
            abort(403, 'رابط الدخول غير صالح.');
        }

        if (!is_array($payload)) {
            abort(403, 'تعذر قراءة بيانات الرابط.');
        }

        $chefEmail = strtolower(trim((string) ($payload['chef_email'] ?? '')));
        $meetLink = (string) ($payload['meet_link'] ?? '');
        $workshopId = (int) ($payload['workshop_id'] ?? 0);
        $expiresAt = isset($payload['expires_at']) ? (int) $payload['expires_at'] : null;

        if (
            $chefEmail === ''
            || !filter_var($chefEmail, FILTER_VALIDATE_EMAIL)
            || $meetLink === ''
            || !filter_var($meetLink, FILTER_VALIDATE_URL)
            || $workshopId <= 0
        ) {
            abort(403, 'بيانات الرابط ناقصة.');
        }

        if ($expiresAt && now()->timestamp > $expiresAt) {
            abort(403, 'انتهت صلاحية الرابط. يرجى إعادة فتح غرفة الاستضافة داخل وصفة للحصول على رابط محدث.');
        }

        $chefColumns = User::columnsForHostContext();

        $host = Workshop::query()
            ->with([
                'chef' => fn ($query) => $query->select($chefColumns),
            ])
            ->find($workshopId);

        if (!$host || !$host->meeting_link) {
            abort(404);
        }

        $expectedEmail = $host->hostGoogleEmail();

        if (!$expectedEmail || !hash_equals($expectedEmail, $chefEmail)) {
            abort(403, 'هذا الرابط مخصص لمضيف مختلف.');
        }

        $hostDomain = parse_url($meetLink, PHP_URL_HOST);

        if (!is_string($hostDomain) || !str_contains($hostDomain, 'meet.google.com')) {
            abort(403, 'الرابط لا يشير إلى اجتماع Google Meet موثوق.');
        }

        $eventId = (string) ($payload['meeting_event_id'] ?? $host->meeting_event_id ?? '');
        $calendarId = $payload['calendar_id'] ?? $host->meeting_calendar_id;
        $hostCredentials = $host->hostGoogleMeetCredentials();

        if ($eventId !== '') {
            $attendeeStatus = $this->googleMeetService->eventHasAttendee($eventId, $chefEmail, $calendarId, $hostCredentials);

            if ($attendeeStatus === false) {
                $hostPayload = $host->hostAttendeePayload();

                if ($hostPayload) {
                    $hostPayload['organizer'] = true;
                    $attendeeStatus = $this->googleMeetService->ensureAttendeePresent(
                        $eventId,
                        $hostPayload,
                        $calendarId,
                        $hostCredentials
                    );
                }

                if ($attendeeStatus === false) {
                    $this->meetingAttendeeSyncService->sync($host);
                    $attendeeStatus = $this->googleMeetService->eventHasAttendee($eventId, $chefEmail, $calendarId, $hostCredentials);
                }

                if ($attendeeStatus === false) {
                    abort(403, 'بريد Google هذا غير مسجل ضمن الاجتماع بعد. يرجى إعادة المحاولة لاحقاً أو التواصل مع الدعم.');
                }
            }
        }

        return redirect()->away(
            GoogleMeetAccountChooser::build($chefEmail, $meetLink, app()->getLocale())
        );
    }
}
