<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Services\Payments\StripeClient;
use App\Support\NotificationCopy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class StripeBookingController extends Controller
{
    public function __construct(
        protected StripeClient $client,
    ) {
        $this->middleware('auth');
    }

    public function createIntent(Request $request): JsonResponse
    {
        $this->ensureGatewayIsReady();

        $data = $request->validate([
            'workshop_id' => ['required', 'exists:workshops,id'],
        ]);

        $user = $request->user();
        $workshop = Workshop::active()->findOrFail($data['workshop_id']);

        $this->ensureWorkshopIsBookable($workshop);
        $this->ensurePriceAllowsOnlinePayment($workshop);

        $existingBooking = WorkshopBooking::where('workshop_id', $workshop->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingBooking && $existingBooking->status === 'confirmed' && $existingBooking->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'workshop_id' => __('Ù„Ù‚Ø¯ Ø£ÙƒÙ…Ù„Øª Ø­Ø¬Ø² Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø© Ø¨Ø§Ù„ÙØ¹Ù„.'),
            ]);
        }

        try {
            $intent = $this->client->createPaymentIntent(
                amount: (float) $workshop->price,
                currency: $workshop->currency ?? config('finance.default_currency', 'JOD'),
                metadata: [
                    'reference_id' => 'workshop_' . $workshop->id,
                    'user_id' => (string) $user->id,
                    'workshop_id' => (string) $workshop->id,
                    'description' => sprintf('Ø­Ø¬Ø² ÙˆØ±Ø´Ø©: %s', $workshop->title),
                ],
            );
        } catch (Throwable $exception) {
            Log::error('Failed to create Stripe payment intent.', [
                'message' => $exception->getMessage(),
                'user_id' => $user->id,
                'workshop_id' => $workshop->id,
            ]);

            return response()->json([
                'message' => 'ØªØ¹Ø°Ø± ØªØ¬Ù‡ÙŠØ² Ø¹Ù…Ù„ÙŠØ© Ø§Ù„Ø¯ÙØ¹. ÙŠØ±Ø¬Ù‰ Ø§Ù„Ù…Ø­Ø§ÙˆÙ„Ø© Ù„Ø§Ø­Ù‚Ø§Ù‹.',
            ], 422);
        }

        return response()->json([
            'client_secret' => $intent['client_secret'] ?? null,
            'payment_intent_id' => $intent['id'] ?? null,
            'publishable_key' => $this->client->getPublicKey(),
            'amount' => $intent['amount'] ?? null,
            'currency' => isset($intent['currency']) ? strtoupper($intent['currency']) : null,
            'zero_decimal_currency' => isset($intent['currency'])
                ? $this->client->isZeroDecimalCurrency($intent['currency'])
                : null,
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $this->ensureGatewayIsReady();

        $data = $request->validate([
            'workshop_id' => ['required', 'exists:workshops,id'],
            'payment_intent_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $workshop = Workshop::active()->findOrFail($data['workshop_id']);

        $this->ensureWorkshopIsBookable($workshop);
        $this->ensurePriceAllowsOnlinePayment($workshop);

        $existingBooking = WorkshopBooking::where('workshop_id', $workshop->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingBooking && $existingBooking->status === 'confirmed' && $existingBooking->payment_status === 'paid') {
            return $this->successResponse($existingBooking, 'ØªÙ… ØªØ£ÙƒÙŠØ¯ Ø­Ø¬Ø²Ùƒ Ù…Ø³Ø¨Ù‚Ø§Ù‹.');
        }

        try {
            $intent = $this->client->retrievePaymentIntent($data['payment_intent_id']);
        } catch (Throwable $exception) {
            Log::error('Failed to retrieve Stripe payment intent.', [
                'message' => $exception->getMessage(),
                'user_id' => $user->id,
                'workshop_id' => $workshop->id,
                'payment_intent_id' => $data['payment_intent_id'],
            ]);

            return response()->json([
                'message' => 'ØªØ¹Ø°Ø± Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ø¹Ù…Ù„ÙŠØ© Ø§Ù„Ø¯ÙØ¹. ÙŠØ±Ø¬Ù‰ Ø§Ù„Ù…Ø­Ø§ÙˆÙ„Ø© Ù…Ø±Ø© Ø£Ø®Ø±Ù‰.',
            ], 422);
        }

        $status = $intent['status'] ?? null;

        if ($status !== 'succeeded') {
            return response()->json([
                'message' => 'Ù„Ù… ÙŠØªÙ… ØªØ£ÙƒÙŠØ¯ Ø¹Ù…Ù„ÙŠØ© Ø§Ù„Ø¯ÙØ¹ Ø¨Ø¹Ø¯. ÙŠØ±Ø¬Ù‰ Ø§Ù„Ù…Ø­Ø§ÙˆÙ„Ø© Ù…Ø¬Ø¯Ø¯Ø§Ù‹.',
            ], 422);
        }

        $currency = strtoupper($intent['currency'] ?? ($workshop->currency ?? config('finance.default_currency', 'JOD')));
        $amountReceived = $intent['amount_received'] ?? $intent['amount'];

        if ($amountReceived === null) {
            Log::warning('Stripe payment intent missing amount.', [
                'payment_intent_id' => $data['payment_intent_id'],
            ]);

            return response()->json([
                'message' => 'Ù„Ù… Ù†ØªÙ…ÙƒÙ† Ù…Ù† Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† Ù‚ÙŠÙ…Ø© Ø§Ù„Ø¯ÙØ¹. ØªÙˆØ§ØµÙ„ Ù…Ø¹ Ø§Ù„Ø¯Ø¹Ù….',
            ], 422);
        }

        $capturedAmount = $this->client->normalizeAmountFromStripe($amountReceived, $currency);
        $expectedAmount = (float) $workshop->price;

        if ($expectedAmount > 0 && abs($capturedAmount - $expectedAmount) > 0.49) {
            Log::warning('Stripe amount mismatch.', [
                'payment_intent_id' => $data['payment_intent_id'],
                'expected' => $expectedAmount,
                'captured' => $capturedAmount,
            ]);

            return response()->json([
                'message' => 'Ù‚ÙŠÙ…Ø© Ø§Ù„Ø¯ÙØ¹ Ù„Ø§ ØªØ·Ø§Ø¨Ù‚ Ø³Ø¹Ø± Ø§Ù„ÙˆØ±Ø´Ø©. ØªÙ… Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ø¹Ù…Ù„ÙŠØ© ØªÙ„Ù‚Ø§Ø¦ÙŠØ§Ù‹.',
            ], 422);
        }

        $booking = DB::transaction(function () use ($existingBooking, $user, $workshop, $intent, $capturedAmount, $currency) {
            $booking = $existingBooking;

            if (! $booking) {
                $booking = WorkshopBooking::create([
                    'workshop_id' => $workshop->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'booking_date' => now(),
                    'payment_status' => 'pending',
                    'payment_method' => 'stripe',
                    'payment_amount' => $capturedAmount,
                    'payment_currency' => $currency,
                    'notes' => 'ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø­Ø¬Ø² Ø¨Ø¹Ø¯ Ø§Ù„Ø¯ÙØ¹ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ.',
                ]);
            }

            $booking->forceFill([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'payment_reference' => $intent['id'] ?? null,
                'payment_payload' => $intent,
                'payment_amount' => $capturedAmount,
                'payment_currency' => $currency,
                'confirmed_at' => now(),
                'booking_date' => $booking->booking_date ?? now(),
                'notes' => $booking->notes ?: 'ØªÙ… Ø§Ù„Ø¯ÙØ¹ Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠØ§Ù‹ Ø¹Ø¨Ø± Stripe.',
            ])->save();

            return $booking->fresh();
        });

        $this->notifyParticipant($booking, $workshop);

        return $this->successResponse($booking, 'ØªÙ… ØªØ£ÙƒÙŠØ¯ Ø­Ø¬Ø²Ùƒ Ø¨Ù†Ø¬Ø§Ø­ Ø¨Ø¹Ø¯ Ø§Ù„Ø¯ÙØ¹ Ø¹Ø¨Ø± Stripe.');
    }

    protected function ensureGatewayIsReady(): void
    {
        if (! $this->client->isEnabled()) {
            abort(503, 'Ø§Ù„Ø¯ÙØ¹ Ø¹Ø¨Ø± Stripe ØºÙŠØ± Ù…ÙØ¹Ù„ Ø­Ø§Ù„ÙŠØ§Ù‹.');
        }
    }

    /**
     * @throws ValidationException
     */
    protected function ensureWorkshopIsBookable(Workshop $workshop): void
    {
        if (! $workshop->is_active) {
            throw ValidationException::withMessages([
                'workshop_id' => 'Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…ØªØ§Ø­Ø© Ù„Ù„Ø­Ø¬Ø² Ø­Ø§Ù„ÙŠØ§Ù‹.',
            ]);
        }

        if ($workshop->is_completed) {
            throw ValidationException::withMessages([
                'workshop_id' => 'Ø§Ù†ØªÙ‡Øª Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø© Ø¨Ø§Ù„ÙØ¹Ù„.',
            ]);
        }

        if ($workshop->is_fully_booked) {
            throw ValidationException::withMessages([
                'workshop_id' => 'Ø¹Ø°Ø±Ø§Ù‹ØŒ ØªÙ… Ø§ÙƒØªÙ…Ø§Ù„ Ø§Ù„Ø¹Ø¯Ø¯ ÙÙŠ Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø©.',
            ]);
        }

        if (! $workshop->is_registration_open) {
            throw ValidationException::withMessages([
                'workshop_id' => 'Ø§Ù†ØªÙ‡Ù‰ Ù…ÙˆØ¹Ø¯ Ø§Ù„ØªØ³Ø¬ÙŠÙ„ Ù„Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø©.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function ensurePriceAllowsOnlinePayment(Workshop $workshop): void
    {
        if ((float) $workshop->price <= 0) {
            throw ValidationException::withMessages([
                'workshop_id' => 'Ù„Ø§ ÙŠÙ…ÙƒÙ† ØªÙØ¹ÙŠÙ„ Ø§Ù„Ø¯ÙØ¹ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ Ù„ÙˆØ±Ø´Ø© Ù…Ø¬Ø§Ù†ÙŠØ©.',
            ]);
        }
    }

    protected function notifyParticipant(WorkshopBooking $booking, Workshop $workshop): void
    {
        [$title, $message] = NotificationCopy::bookingConfirmed($booking, $workshop);

        Notification::createNotification(
            $booking->user_id,
            'workshop_confirmed',
            $title,
            $message,
            [
                'workshop_id' => $workshop->id,
                'workshop_slug' => $workshop->slug,
                'booking_id' => $booking->id,
                'action_url' => route('bookings.show', $booking),
            ]
        );
    }

    protected function successResponse(WorkshopBooking $booking, string $message): JsonResponse
    {
        $booking->loadMissing('workshop');
        $workshop = $booking->workshop;

        $joinUrl = null;

        if ($workshop && $workshop->is_online && $workshop->meeting_link && $booking->public_code) {
            $joinUrl = $booking->secure_join_url;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'booking_id' => $booking->id,
            'redirect_url' => route('bookings.show', $booking),
            'join_url' => $joinUrl,
        ]);
    }
}

