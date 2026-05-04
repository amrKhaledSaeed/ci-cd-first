<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Exceptions\Domain\CartOperationException;
use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Smpita\TypeAs\TypeAs;

class CartService
{
    public function __construct(private ProductRepository $productRepository) {}

    /**
     * @return array{lines: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function summary(): array
    {
        $syncResult = $this->syncLines();
        $normalizedLines = array_values($syncResult['lines']);
        $subtotal = array_reduce(
            $normalizedLines,
            fn (float $carry, array $line): float => $carry + TypeAs::float($line['subtotal'] ?? 0),
            0.0,
        );
        $itemsCount = array_reduce(
            $normalizedLines,
            fn (int $carry, array $line): int => $carry + TypeAs::int($line['quantity'] ?? 0),
            0,
        );

        return [
            'lines' => $normalizedLines,
            'meta' => [
                'items_count' => $itemsCount,
                'subtotal' => round($subtotal, 2),
                'stale_removed_count' => $syncResult['stale_removed_count'],
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }

    public function add(int $productId, int $quantity): void
    {
        $product = $this->productRepository->findAvailableForCart($productId, $quantity);

        if ($product === null) {
            throw new CartOperationException(__('The selected product is unavailable or out of stock.'));
        }

        $syncResult = $this->syncLines();
        $lines = $syncResult['lines'];
        $existingLine = TypeAs::array(Arr::get($lines, $productId, []), default: []);

        $currentQuantity = TypeAs::int($existingLine['quantity'] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $product->stock) {
            throw new CartOperationException(__('Requested quantity exceeds available stock.'));
        }

        $lines[$productId] = $this->lineData(
            productId: $product->id,
            name: $product->name,
            slug: $product->slug,
            unitPrice: TypeAs::float($product->price),
            quantity: $newQuantity,
        );

        Session::put('cart.lines', $lines);
    }

    public function update(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($productId);

            return;
        }

        $product = $this->productRepository->findAvailableForCart($productId, $quantity);

        if ($product === null) {
            throw new CartOperationException(__('The selected product is unavailable or out of stock.'));
        }

        $syncResult = $this->syncLines();
        $lines = $syncResult['lines'];
        $lines[$productId] = $this->lineData(
            productId: $product->id,
            name: $product->name,
            slug: $product->slug,
            unitPrice: TypeAs::float($product->price),
            quantity: $quantity,
        );

        Session::put('cart.lines', $lines);
    }

    public function remove(int $productId): void
    {
        $syncResult = $this->syncLines();
        $lines = $syncResult['lines'];
        unset($lines[$productId]);

        Session::put('cart.lines', $lines);
    }

    public function clear(): void
    {
        Session::forget('cart.lines');
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

    /**
     * @return array{lines: array<int, array<string, mixed>>, stale_removed_count: int}
     */
    private function syncLines(): array
    {
        /** @var array<int, array<string, mixed>> $sessionLines */
        $sessionLines = TypeAs::array(Session::get('cart.lines', []), default: []);
        $staleRemovedCount = 0;

        if ($sessionLines === []) {
            return [
                'lines' => [],
                'stale_removed_count' => 0,
            ];
        }

        $productIds = array_map(
            fn (int|string $key): int => TypeAs::int($key),
            array_keys($sessionLines),
        );
        $products = $this->productRepository
            ->findActiveByIds($productIds)
            ->keyBy('id');

        $normalized = [];

        foreach ($sessionLines as $sessionKey => $line) {
            $productId = TypeAs::int($line['product_id'] ?? $sessionKey);
            $product = $products->get($productId);

            if (! $product || $product->stock < 1) {
                $staleRemovedCount++;

                continue;
            }

            $requestedQuantity = TypeAs::int($line['quantity'] ?? 1);
            $quantity = min($requestedQuantity, $product->stock);

            if ($quantity < 1) {
                $staleRemovedCount++;

                continue;
            }

            if ($quantity !== $requestedQuantity) {
                $staleRemovedCount++;
            }

            $normalized[$productId] = $this->lineData(
                productId: $productId,
                name: TypeAs::string($product->name),
                slug: TypeAs::string($product->slug),
                unitPrice: TypeAs::float($product->price),
                quantity: $quantity,
            );
        }

        Session::put('cart.lines', $normalized);

        return [
            'lines' => $normalized,
            'stale_removed_count' => $staleRemovedCount,
        ];
    }
}
