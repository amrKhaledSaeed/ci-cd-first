<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\User;
use App\Repositories\Cart\CartItemRepository;
use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Smpita\TypeAs\TypeAs;

class PersistedCartService
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private ProductRepository $productRepository,
    ) {}

    public function mergeFromSessionForUser(?User $user = null): void
    {
        $resolvedUser = $this->resolveUser($user);

        if ($resolvedUser === null) {
            return;
        }

        $sessionQuantities = $this->sessionQuantities();
        $storedItems = $this->cartItemRepository->forUser($resolvedUser->id);

        /** @var array<int, int> $storedQuantities */
        $storedQuantities = [];

        foreach ($storedItems as $storedItem) {
            $storedQuantities[$storedItem->product_id] = TypeAs::int($storedItem->quantity);
        }

        $allProductIds = array_values(array_unique([
            ...array_keys($sessionQuantities),
            ...array_keys($storedQuantities),
        ]));

        if ($allProductIds === []) {
            $this->cartItemRepository->clearForUser($resolvedUser->id);
            Session::forget('cart.lines');

            return;
        }

        $products = $this->productRepository
            ->findActiveByIds($allProductIds)
            ->keyBy('id');

        $mergedLines = [];
        $mergedQuantities = [];

        foreach ($allProductIds as $productId) {
            $product = $products->get($productId);

            if (! $product || $product->stock < 1) {
                continue;
            }

            $sessionQuantity = TypeAs::int($sessionQuantities[$productId] ?? 0);
            $storedQuantity = TypeAs::int($storedQuantities[$productId] ?? 0);
            $quantity = min($product->stock, $sessionQuantity + $storedQuantity);

            if ($quantity < 1) {
                continue;
            }

            $mergedLines[$productId] = $this->lineData(
                productId: $product->id,
                name: $product->name,
                slug: $product->slug,
                unitPrice: TypeAs::float($product->price),
                quantity: $quantity,
            );

            $mergedQuantities[$productId] = $quantity;
        }

        Session::put('cart.lines', $mergedLines);
        $this->cartItemRepository->syncForUser($resolvedUser->id, $mergedQuantities);
    }

    public function persistSessionForUser(?User $user = null): void
    {
        $resolvedUser = $this->resolveUser($user);

        if ($resolvedUser === null) {
            return;
        }

        $sessionQuantities = $this->sessionQuantities();

        if ($sessionQuantities === []) {
            $this->cartItemRepository->clearForUser($resolvedUser->id);

            return;
        }

        $products = $this->productRepository
            ->findActiveByIds(array_keys($sessionQuantities))
            ->keyBy('id');

        $persistableQuantities = [];

        foreach ($sessionQuantities as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product || $product->stock < 1) {
                continue;
            }

            $persistableQuantities[$productId] = min($product->stock, $quantity);
        }

        $this->cartItemRepository->syncForUser($resolvedUser->id, $persistableQuantities);
    }

    public function clearPersistedForUser(?User $user = null): void
    {
        $resolvedUser = $this->resolveUser($user);

        if ($resolvedUser === null) {
            return;
        }

        $this->cartItemRepository->clearForUser($resolvedUser->id);
    }

    private function resolveUser(?User $user = null): ?User
    {
        if ($user instanceof User) {
            return $user;
        }

        $authUser = Auth::user();

        return $authUser instanceof User ? $authUser : null;
    }

    /**
     * @return array<int, int>
     */
    private function sessionQuantities(): array
    {
        /** @var array<int|string, array<string, mixed>> $lines */
        $lines = TypeAs::array(Session::get('cart.lines', []), default: []);
        $quantities = [];

        foreach ($lines as $key => $line) {
            $productId = TypeAs::int($line['product_id'] ?? $key);
            $quantity = TypeAs::int($line['quantity'] ?? 0);

            if ($quantity < 1) {
                continue;
            }

            $quantities[$productId] = $quantity;
        }

        return $quantities;
    }

    /**
     * @return array{product_id: int, name: string, slug: string, unit_price: float, quantity: int, subtotal: float}
     */
    private function lineData(int $productId, string $name, string $slug, float $unitPrice, int $quantity): array
    {
        return [
            'product_id' => $productId,
            'name' => $name,
            'slug' => $slug,
            'unit_price' => round($unitPrice, 2),
            'quantity' => $quantity,
            'subtotal' => round($unitPrice * $quantity, 2),
        ];
    }
}
