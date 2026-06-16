@extends('layouts.app')

@section('title', 'Shopping cart - Wasfah')

@push('styles')
<style>
    .cart-item {
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
    <livewire:cart-manager />
@endsection

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    if (window.loadCartCount) {
        window.loadCartCount();
    }

    Livewire.on('cart-updated', () => {
        if (window.loadCartCount) {
            window.loadCartCount();
        }
    });
});
</script>
@endpush
