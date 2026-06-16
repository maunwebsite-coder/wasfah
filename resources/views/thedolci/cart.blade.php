@extends('thedolci.layouts.store')

@section('title', 'Cart | thedolci')
@section('body_class', 'dolci-page-cart')

@section('content')
    <livewire:thedolci.cart-manager />
@endsection

@push('scripts')
<script>
const registerDolciCartCountListener = () => {
    if (!window.Livewire || window.__dolciCartCountListenerRegistered) {
        return;
    }

    window.__dolciCartCountListenerRegistered = true;

    window.Livewire.on('thedolci-cart-count-updated', (payload) => {
        const count = Number(payload?.count ?? payload?.[0]?.count ?? 0);

        document.querySelectorAll('.dolci-cart-count').forEach((node) => {
            node.textContent = String(Number.isFinite(count) ? count : 0);
        });
    });
};

registerDolciCartCountListener();
document.addEventListener('livewire:init', registerDolciCartCountListener);
</script>
@endpush
