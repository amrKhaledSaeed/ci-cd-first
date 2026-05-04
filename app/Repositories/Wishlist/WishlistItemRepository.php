<?php

declare(strict_types=1);

namespace App\Repositories\Wishlist;

use App\Models\Product;
use App\Models\WishlistItem;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<WishlistItem>
 */
class WishlistItemRepository extends BaseRepository
{
    public function existsForUserAndProduct(int $userId, int $productId): bool
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    public function add(int $userId, int $productId): void
    {
        $this->query()->firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    public function remove(int $userId, int $productId): void
    {
        $this->query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return Collection<int, WishlistItem>
     */
    public function forUserAndProducts(int $userId, array $productIds): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateProductsForUser(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('wishlistItems', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->latest('id')
            ->paginate($perPage);
    }

    protected function model(): WishlistItem
    {
        return new WishlistItem;
    }
}
