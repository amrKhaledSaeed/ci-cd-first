<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Smpita\TypeAs\TypeAs;

/**
 * @extends BaseRepository<Product>
 */
class ProductRepository extends BaseRepository
{
    public function findById(int $id): ?Product
    {
        $product = $this->find($id);

        return $product instanceof Product ? $product : null;
    }

    /**
     * @param  array{search?: string|null, category_id?: int|null, sort?: string, per_page?: int}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateActive(array $filters = []): LengthAwarePaginator
    {
        $search = TypeAs::nullableString($filters['search'] ?? null);
        $categoryId = TypeAs::nullableInt($filters['category_id'] ?? null);
        $sort = TypeAs::string($filters['sort'] ?? 'relevance');
        $perPage = TypeAs::int($filters['per_page'] ?? 12);

        $query = $this->query()
            ->with('productCategory')
            ->where('is_active', true);

        if ($search !== null && $search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($categoryId !== null && $categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $this->applySort($query, $sort, $search);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->query()
            ->with('productCategory')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function findAvailableForCart(int $productId, int $quantity): ?Product
    {
        return $this->query()
            ->with('productCategory')
            ->whereKey($productId)
            ->where('is_active', true)
            ->where('stock', '>=', $quantity)
            ->first();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return Collection<int, Product>
     */
    public function findActiveByIds(array $productIds): Collection
    {
        if ($productIds === []) {
            return new Collection;
        }

        return $this->query()
            ->with('productCategory')
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get();
    }

    /**
     * @param  array<int, string>  $slugs
     * @return Collection<int, Product>
     */
    public function findActiveBySlugs(array $slugs): Collection
    {
        if ($slugs === []) {
            return new Collection;
        }

        return $this->query()
            ->with('productCategory')
            ->whereIn('slug', $slugs)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function findFallbackRecommendations(Product $product, int $limit = 4): Collection
    {
        return $this->query()
            ->with('productCategory')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->orderByRaw('ABS(price - ?) asc', [$product->price])
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function randomActive(int $limit = 3, array $excludeIds = []): Collection
    {
        $query = $this->query()
            ->with('productCategory')
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($excludeIds !== []) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function recommendationCandidatePool(int $limit = 24, array $excludeIds = []): Collection
    {
        $query = $this->query()
            ->with('productCategory')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->latest('id');

        if ($excludeIds !== []) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query
            ->limit($limit)
            ->get();
    }

    /**
     * @return SupportCollection<int, Category>
     */
    public function activeCategories(): SupportCollection
    {
        /** @var SupportCollection<int, Category> $categories */
        $categories = Category::query()
            ->whereHas('products', fn (Builder $query): Builder => $query->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return $categories;
    }

    protected function model(): Product
    {
        return new Product;
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort, ?string $search): void
    {
        if ($sort === 'price_asc') {
            $query->orderBy('price');

            return;
        }

        if ($sort === 'price_desc') {
            $query->orderByDesc('price');

            return;
        }

        if ($sort === 'newest') {
            $query->latest('id');

            return;
        }

        if ($search !== null && $search !== '') {
            $query
                ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$search.'%'])
                ->orderBy('name');

            return;
        }

        $query->latest('id');
    }
}
