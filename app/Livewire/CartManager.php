<?php

namespace App\Livewire;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class CartManager extends Component
{
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantities();
    }

    public function render()
    {
        $cartItems = $this->cartItems();

        return view('livewire.cart-manager', [
            'cartItems' => $cartItems,
            'total' => round($cartItems->sum('total_price'), 2),
            'totalQuantity' => (int) $cartItems->sum('quantity'),
            'uniqueItems' => $cartItems->count(),
        ]);
    }

    public function increment(int $cartId): void
    {
        $item = $this->findUserCartItem($cartId);

        if (!$item) {
            return;
        }

        $this->applyQuantity($item, min(10, ((int) $item->quantity) + 1));
    }

    public function decrement(int $cartId): void
    {
        $item = $this->findUserCartItem($cartId);

        if (!$item) {
            return;
        }

        $this->applyQuantity($item, max(1, ((int) $item->quantity) - 1));
    }

    public function updateQuantity(int $cartId): void
    {
        $item = $this->findUserCartItem($cartId);

        if (!$item) {
            return;
        }

        $requested = (int) ($this->quantities[$cartId] ?? $item->quantity);
        $quantity = max(1, min(10, $requested));

        $this->applyQuantity($item, $quantity);
    }

    public function removeItem(int $cartId): void
    {
        $item = $this->findUserCartItem($cartId);

        if (!$item) {
            return;
        }

        $item->delete();
        unset($this->quantities[$cartId]);

        $this->dispatch('cart-updated', count: $this->cartCount());
    }

    public function clearCart(): void
    {
        $this->userCartQuery()->delete();
        $this->quantities = [];

        $this->dispatch('cart-updated', count: 0);
    }

    private function applyQuantity(Cart $item, int $quantity): void
    {
        $item->update(['quantity' => $quantity]);
        $this->quantities[$item->id] = $quantity;

        $this->dispatch('cart-updated', count: $this->cartCount());
    }

    private function syncQuantities(): void
    {
        $this->quantities = $this->cartItems()
            ->mapWithKeys(fn (Cart $item) => [$item->id => (int) $item->quantity])
            ->all();
    }

    private function cartItems(): Collection
    {
        $items = $this->userCartQuery()
            ->with('tool')
            ->get();

        // Keep cart price equal to unit tool price.
        foreach ($items as $item) {
            if ($item->tool && (float) $item->price !== (float) $item->tool->price) {
                $item->update(['price' => $item->tool->price]);
            }
        }

        return $items;
    }

    private function cartCount(): int
    {
        return (int) $this->userCartQuery()->sum('quantity');
    }

    private function userCartQuery(): Builder
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        return Cart::query()->forUser($userId, $sessionId);
    }

    private function findUserCartItem(int $cartId): ?Cart
    {
        return $this->userCartQuery()
            ->whereKey($cartId)
            ->first();
    }
}
