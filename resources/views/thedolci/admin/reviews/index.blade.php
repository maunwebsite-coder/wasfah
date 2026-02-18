@extends('thedolci.layouts.admin')

@section('title', 'Reviews | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Customer Review Management</h1>
            <a href="{{ route('thedolci.admin.reviews.create') }}" class="dolci-btn dolci-btn-primary">Add Review</a>
        </div>

        @if(!$dbReady)
            <div class="dolci-alert dolci-alert-error">Database table is not ready. Run migrations first.</div>
        @endif

        <div class="dolci-admin-table-wrap">
            <table class="dolci-admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Rating</th>
                        <th>Featured</th>
                        <th>Visible</th>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>
                                <div>{{ $review->customer_name }}</div>
                                <small>{{ \Illuminate\Support\Str::limit($review->review_text, 80) }}</small>
                            </td>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ $review->is_featured ? 'Yes' : 'No' }}</td>
                            <td>{{ $review->is_active ? 'Yes' : 'No' }}</td>
                            <td>{{ $review->sort_order }}</td>
                            <td class="dolci-admin-actions">
                                <a href="{{ route('thedolci.admin.reviews.edit', $review) }}">Edit</a>
                                <form method="POST" action="{{ route('thedolci.admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No reviews found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection


