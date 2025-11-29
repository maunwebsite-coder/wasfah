<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkshopReviewController extends Controller
{
    /**
     * Store or update a review for a completed workshop.
     */
    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $user = $request->user();

        $hasEligibleBooking = $workshop->hasEligibleBookingForReview($user->id);

        if (! $hasEligibleBooking) {
            return back()->withErrors([
                'rating' => __('workshops.reviews.errors.not_participant'),
            ]);
        }

        if (! $workshop->reviewWindowOpen()) {
            return back()->withErrors([
                'rating' => __('workshops.reviews.errors.not_finished'),
            ]);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $comment = isset($data['comment']) ? trim((string) $data['comment']) : null;

        WorkshopReview::updateOrCreate(
            [
                'workshop_id' => $workshop->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $comment ?: null,
                'is_approved' => true,
            ]
        );

        WorkshopReview::syncWorkshopAggregates($workshop->id);

        return back()->with('review_saved', __('workshops.reviews.flash.submitted'));
    }
}
