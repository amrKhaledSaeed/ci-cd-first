<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\User;
use App\Services\Analytics\StoreAnalyticsService;
use App\Services\Product\ProductService;
use Smpita\TypeAs\TypeAs;

class CartActionService
{
    public function __construct(
        private CartService $cartService,
        private PersistedCartService $persistedCartService,
        private StoreAnalyticsService $storeAnalyticsService,
        private ProductService $productService,
    ) {}

    /**
     * @param  array{product_id: int, quantity: int}  $validated
     */
    public function add(array $validated, ?User $user): void
    {
        $productId = TypeAs::int($validated['product_id']);
        $quantity = TypeAs::int($validated['quantity']);

        $this->cartService->add(
            productId: $productId,
            quantity: $quantity,
        );
        $this->persistedCartService->persistSessionForUser($user);

        $product = $this->productService->findById($productId);

        if ($product === null) {
            return;
        }

        $this->storeAnalyticsService->track(
            eventName: 'add_to_cart',
            user: $user,
            product: $product,
            context: [
                'quantity' => $quantity,
            ],
        );
    }

    public function update(int $productId, int $quantity, ?User $user): void
    {
        $this->cartService->update(
            productId: $productId,
            quantity: $quantity,
        );
        $this->persistedCartService->persistSessionForUser($user);
    }

    public function remove(int $productId, ?User $user): void
    {
        $this->cartService->remove($productId);
        $this->persistedCartService->persistSessionForUser($user);
    }

    public function clear(?User $user): void
    {
        $this->cartService->clear();
        $this->persistedCartService->persistSessionForUser($user);
    }
}
