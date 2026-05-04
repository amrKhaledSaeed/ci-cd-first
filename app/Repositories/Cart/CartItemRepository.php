<?php

declare(strict_types=1);

namespace App\Repositories\Cart;

use App\Models\CartItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<CartItem>
 */
class CartItemRepository extends BaseRepository
{
    /**
     * @return Collection<int, CartItem>
     */
    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * @param  array<int, int>  $productQuantities
     */
    public function syncForUser(int $userId, array $productQuantities): void
    {
        $this->clearForUser($userId);

        if ($productQuantities === []) {
            return;
        }

        $now = now();

        $rows = [];

        foreach ($productQuantities as $productId => $quantity) {
            if ($quantity < 1) {
                continue;
            }

            $rows[] = [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            $this->model()->newQuery()->insert($rows);
        }
    }

    public function clearForUser(int $userId): void
    {
        $this->query()
            ->where('user_id', $userId)
            ->delete();
    }

    protected function model(): CartItem
    {
        return new CartItem;
    }
}
