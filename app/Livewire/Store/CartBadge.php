<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Smpita\TypeAs\TypeAs;

class CartBadge extends Component
{
    public int $count = 0;

    public function mount(CartService $cartService): void
    {
        $this->count = $this->resolveCount($cartService);
    }

    #[On('cart-updated')]
    public function refreshCount(CartService $cartService): void
    {
        $this->count = $this->resolveCount($cartService);
    }

    public function render(): View
    {
        return view('livewire.store.cart-badge');
    }

    private function resolveCount(CartService $cartService): int
    {
        $summary = $cartService->summary();

        return TypeAs::int($summary['meta']['items_count'] ?? 0);
    }
}
