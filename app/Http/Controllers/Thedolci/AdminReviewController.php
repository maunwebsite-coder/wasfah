<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Schema::hasTable('thedolci_reviews')
            ? ThedolciReview::query()->orderBy('sort_order')->orderByDesc('created_at')->get()
            : collect();

        return view('thedolci.admin.reviews.index', [
            'reviews' => $reviews,
            'dbReady' => Schema::hasTable('thedolci_reviews'),
        ]);
    }

    public function create(): View
    {
        return view('thedolci.admin.reviews.form', [
            'review' => new ThedolciReview(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('thedolci_reviews')) {
            return back()->withErrors(['db' => 'Run migrations first to manage reviews.']);
        }

        $data = $this->validatePayload($request);

        ThedolciReview::query()->create($data);

        return redirect()->route('thedolci.admin.reviews.index')->with('success', 'Review created.');
    }

    public function edit(ThedolciReview $review): View
    {
        return view('thedolci.admin.reviews.form', [
            'review' => $review,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, ThedolciReview $review): RedirectResponse
    {
        if (! Schema::hasTable('thedolci_reviews')) {
            return back()->withErrors(['db' => 'Run migrations first to manage reviews.']);
        }

        $data = $this->validatePayload($request);
        $review->update($data);

        return redirect()->route('thedolci.admin.reviews.index')->with('success', 'Review updated.');
    }

    public function destroy(ThedolciReview $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('thedolci.admin.reviews.index')->with('success', 'Review deleted.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'string', 'max:2000'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'customer_name' => trim($validated['customer_name']),
            'rating' => (int) $validated['rating'],
            'review_text' => trim($validated['review_text']),
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }
}

