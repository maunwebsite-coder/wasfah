@extends('thedolci.layouts.admin')

@section('title', ($isEdit ? 'Edit' : 'Create') . ' Review | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <h1>{{ $isEdit ? 'Edit Review' : 'Add Review' }}</h1>

        <form method="POST" action="{{ $isEdit ? route('thedolci.admin.reviews.update', $review) : route('thedolci.admin.reviews.store') }}" class="dolci-form-card">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="dolci-form-grid-2">
                <div>
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $review->customer_name) }}" required>
                </div>
                <div>
                    <label>Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $review->rating ?? 5) }}" required>
                </div>
            </div>

            <label>Review Text</label>
            <textarea name="review_text" rows="5" required>{{ old('review_text', $review->review_text) }}</textarea>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $review->sort_order ?? 0) }}">
                </div>
                <div class="dolci-check-stack">
                    <label><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $review->is_featured) ? 'checked' : '' }}> Show in featured reviews</label>
                    <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $review->is_active ?? true) ? 'checked' : '' }}> Visible on website</label>
                </div>
            </div>

            <button type="submit" class="dolci-btn dolci-btn-primary">{{ $isEdit ? 'Update Review' : 'Create Review' }}</button>
        </form>
    </div>
</section>
@endsection


