<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Exceptions\Domain\CartOperationException;
use App\Rules\CartQuantityWithinStock;
use App\Services\Cart\CartActionService;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;
use Smpita\TypeAs\TypeAs;

class CartPanel extends Component
{
    /**
     * @var array{lines: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public array $cart = [
        'lines' => [],
        'meta' => [],
    ];

    /**
     * @var array<int, int>
     */
    public array $quantities = [];

    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $summary = app(CartService::class)->summary();

        $this->cart = $summary;
        $this->quantities = collect($summary['lines'])
            ->mapWithKeys(fn (array $line): array => [
                TypeAs::int($line['product_id']) => TypeAs::int($line['quantity']),
            ])
            ->all();
    }

    public function increment(int $productId): void
    {
        $currentQuantity = TypeAs::int($this->quantities[$productId] ?? 0);
        $this->quantities[$productId] = $currentQuantity + 1;
    }

    public function decrement(int $productId): void
    {
        $currentQuantity = TypeAs::int($this->quantities[$productId] ?? 0);
        $this->quantities[$productId] = max($currentQuantity - 1, 0);
    }

    public function updateLine(int $productId): void
    {
        $quantity = TypeAs::int($this->quantities[$productId] ?? 0);
        $errorKey = 'quantities.'.$productId;

        $this->resetErrorBag($errorKey);

        $validator = Validator::make(
            ['quantity' => $quantity],
            ['quantity' => ['required', 'integer', 'min:0', new CartQuantityWithinStock($productId)]],
            [
                'quantity.required' => __('Please provide a quantity.'),
                'quantity.integer' => __('Quantity must be a number.'),
                'quantity.min' => __('Quantity cannot be negative.'),
            ],
        );

        if ($validator->fails()) {
            $this->addError($errorKey, TypeAs::string($validator->errors()->first('quantity')));

            return;
        }

        $resolvedUser = auth()->check() ? user() : null;

        try {
            app(CartActionService::class)->update($productId, $quantity, $resolvedUser);
        } catch (CartOperationException $exception) {
            $this->addError($errorKey, $exception->getMessage());

            return;
        }

        $this->statusMessage = __('Cart updated.');
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function removeLine(int $productId): void
    {
        $resolvedUser = auth()->check() ? user() : null;
        app(CartActionService::class)->remove($productId, $resolvedUser);
        $this->statusMessage = __('Item removed from cart.');
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        $resolvedUser = auth()->check() ? user() : null;
        app(CartActionService::class)->clear($resolvedUser);
        $this->statusMessage = __('Cart cleared.');
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.store.cart-panel');
    }
}
