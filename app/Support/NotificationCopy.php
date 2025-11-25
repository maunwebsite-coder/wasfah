<?php

namespace App\Support;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use Carbon\CarbonInterface;

class NotificationCopy
{
    /**
     * Copy for pending booking notifications.
     */
    public static function bookingPending(WorkshopBooking $booking, ?Workshop $workshop = null): array
    {
        $workshop = $workshop ?? $booking->workshop;
        $title = 'We received your booking request';
        $workshopLabel = static::workshopLabel($workshop);
        $schedule = static::formatSchedule($workshop);
        $location = static::describeLocation($workshop);

        $message = "You are on the waiting list for {$workshopLabel}.";

        if ($schedule) {
            $message .= " The session is planned for {$schedule}.";
        }

        if ($location) {
            $message .= " Location: {$location}.";
        }

        $message .= ' We will confirm your seat shortly and post the update here. Track the status anytime from the My Bookings page.';

        return [$title, $message];
    }

    /**
     * Copy for confirmed booking notifications.
     */
    public static function bookingConfirmed(WorkshopBooking $booking, ?Workshop $workshop = null): array
    {
        $workshop = $workshop ?? $booking->workshop;
        $title = 'Your workshop seat is confirmed';
        $workshopLabel = static::workshopLabel($workshop);
        $schedule = static::formatSchedule($workshop);
        $location = static::describeLocation($workshop);

        $message = "You're confirmed for {$workshopLabel}";

        if ($schedule) {
            $message .= " on {$schedule}";
        }

        $message .= '. ';

        if ($workshop && $workshop->is_online) {
            $message .= 'Open the booking card to grab the meeting link and join a few minutes early.';
        } else {
            $message .= 'Please arrive 10 minutes early so we can set up comfortably.';
        }

        if ($location) {
            $message .= " Location: {$location}.";
        }

        $message .= ' Need to make changes? Use the booking page to reach the ' . static::brandName() . ' support team.';

        return [$title, $message];
    }

    /**
     * Copy for cancelled booking notifications.
     */
    public static function bookingCancelled(WorkshopBooking $booking, ?string $reason = null, ?Workshop $workshop = null): array
    {
        $workshop = $workshop ?? $booking->workshop;
        $title = 'Workshop booking cancelled';
        $workshopLabel = static::workshopLabel($workshop);
        $schedule = static::formatSchedule($workshop);

        $message = "Your booking for {$workshopLabel}";

        if ($schedule) {
            $message .= " scheduled for {$schedule}";
        }

        $message .= ' was cancelled.';

        if ($reason) {
            $message .= " Reason: {$reason}.";
        }

        $message .= ' Browse the workshops page to pick another slot that fits your calendar.';

        return [$title, $message];
    }

    /**
     * Copy for admin review notifications.
     */
    public static function workshopReviewRequired(string $chefName, Workshop $workshop): array
    {
        $title = 'New workshop waiting for review';
        $message = "{$chefName} submitted \"{$workshop->title}\" and it is waiting for your approval before it can go live.";

        return [$title, $message];
    }

    /**
     * Copy for welcome notifications.
     */
    public static function welcome(User $user): array
    {
        $brand = static::brandName();
        $firstName = trim(explode(' ', $user->name ?? '')[0] ?? '');
        $title = $firstName !== '' ? "Welcome to {$brand}, {$firstName}" : "Welcome to {$brand}";
        $message = 'We are happy you are here. Save your favorite recipes, manage bookings, and receive live reminders in one clean ' . $brand . ' dashboard. Start by updating your profile so we can personalize your feed.';

        return [$title, $message];
    }

    protected static function workshopLabel(?Workshop $workshop): string
    {
        return $workshop && $workshop->title
            ? '"' . $workshop->title . '"'
            : 'your workshop';
    }

    protected static function formatSchedule(?Workshop $workshop): ?string
    {
        $date = $workshop?->start_date;

        if (! $date instanceof CarbonInterface) {
            return null;
        }

        $timezone = config('app.display_timezone')
            ?? config('app.timezone')
            ?? $date->getTimezone()->getName()
            ?? 'UTC';

        return $date->copy()
            ->setTimezone($timezone)
            ->format('M j, Y \\a\\t g:i A');
    }

    protected static function describeLocation(?Workshop $workshop): ?string
    {
        if (! $workshop) {
            return null;
        }

        if ($workshop->is_online) {
            return 'online (links live inside your booking)';
        }

        if ($workshop->location) {
            return $workshop->location;
        }

        if ($workshop->address) {
            return $workshop->address;
        }

        return null;
    }

    protected static function brandName(): string
    {
        return config('app.name', 'Peahskill');
    }
}
