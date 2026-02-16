@extends('thedolci.layouts.admin')

@section('title', 'Products | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Product Management</h1>
            <div class="dolci-admin-head-actions">
                <form method="POST" action="{{ route('thedolci.admin.products.seed-defaults') }}">
                    @csrf
                    <button type="submit" class="dolci-btn dolci-btn-secondary">Import Defaults</button>
                </form>
                <a href="{{ route('thedolci.admin.products.create') }}" class="dolci-btn dolci-btn-primary">Add Product</a>
            </div>
        </div>

        @if(!$dbReady)
            <div class="dolci-alert dolci-alert-error">Database table is not ready. Run migrations first.</div>
        @endif

        <div class="dolci-admin-table-wrap">
            <table class="dolci-admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Seasonal</th>
                        <th>Limited Edition</th>
                        <th>Active</th>
                        <th>Stock (limited)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->slug }}</td>
                            <td>{{ $product->is_seasonal ? 'Yes' : 'No' }}</td>
                            <td>{{ $product->show_limited_edition ? 'Yes' : 'No' }}</td>
                            <td>{{ $product->is_active ? 'Yes' : 'No' }}</td>
                            <td>{{ $product->limited_quantity ?? '-' }}</td>
                            <td class="dolci-admin-actions">
                                <a href="{{ route('thedolci.admin.products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('thedolci.admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

