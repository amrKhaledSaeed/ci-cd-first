<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Exceptions\Domain\CartOperationException;
use App\Services\Cart\CartActionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Smpita\TypeAs\TypeAs;

class AddToCartForm extends Component
{
    public int $productId;

    public int $stock;

    public int $quantity = 1;

    public ?string $statusMessage = null;

    public function mount(int $productId, int $stock): void
    {
        $this->productId = $productId;
        $this->stock = $stock;
        $this->quantity = $stock > 0 ? 1 : 0;
    }

    public function increment(): void
    {
        if ($this->stock < 1) {
            return;
        }

        $this->quantity = min($this->quantity + 1, $this->stock);
    }

    public function decrement(): void
    {
        if ($this->stock < 1) {
            return;
        }

        $this->quantity = max($this->quantity - 1, 1);
    }

    public function addToCart(CartActionService $cartActionService): void
    {
        $this->statusMessage = null;
        $this->resetErrorBag('quantity');

        $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ], messages: [
            'quantity.required' => __('Please select a quantity.'),
            'quantity.integer' => __('Quantity must be a number.'),
            'quantity.min' => __('Quantity must be at least 1.'),
        ]);

        $resolvedUser = auth()->check() ? user() : null;

        try {
            $cartActionService->add([
                'product_id' => $this->productId,
                'quantity' => TypeAs::int($this->quantity),
            ], $resolvedUser);
        } catch (CartOperationException $exception) {
            $this->addError('quantity', $exception->getMessage());

            return;
        }

        $this->dispatch('cart-updated');
        $this->statusMessage = __('Product added to cart.');
    }

    public function render(): View
    {
        return view('livewire.store.add-to-cart-form');
    }
}
