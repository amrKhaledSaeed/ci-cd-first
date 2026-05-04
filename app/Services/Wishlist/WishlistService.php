<?php

declare(strict_types=1);

namespace App\Services\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Wishlist\WishlistItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Smpita\TypeAs\TypeAs;

class WishlistService
{
    public function __construct(private WishlistItemRepository $wishlistItemRepository) {}

    public function addActive(User $user, Product $product): bool
    {
        if (! $product->is_active) {
            return false;
        }

        $this->add($user, $product);

        return true;
    }

    public function add(User $user, Product $product): void
    {
        $this->wishlistItemRepository->add($user->id, $product->id);
        Cache::forget($this->containsCacheKey($user, $product));
    }

    public function remove(User $user, Product $product): void
    {
        $this->wishlistItemRepository->remove($user->id, $product->id);
        Cache::forget($this->containsCacheKey($user, $product));
    }

    public function contains(User $user, Product $product): bool
    {
        return (bool) Cache::remember(
            $this->containsCacheKey($user, $product),
            now()->addMinutes(5),
            fn (): bool => $this->wishlistItemRepository->existsForUserAndProduct($user->id, $product->id),
        );
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, int>
     */
    public function productIdsFor(User $user, array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $items = $this->wishlistItemRepository->forUserAndProducts($user->id, $productIds);

        /** @var array<int, int> $ids */
        $ids = $items
            ->pluck('product_id')
            ->map(fn (mixed $productId): int => TypeAs::int($productId))
            ->values()
            ->all();

        return $ids;
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateProducts(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->wishlistItemRepository->paginateProductsForUser($user->id, $perPage);
    }

    private function containsCacheKey(User $user, Product $product): string
    {
        return sprintf('wishlist:contains:%d:%d', $user->id, $product->id);
    }
}
