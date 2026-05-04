<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Data\Product\ProductListData;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(private ProductRepository $productRepository) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function listActiveProducts(ProductListData $productListData): LengthAwarePaginator
    {
        return $this->productRepository->paginateActive($productListData->toArray());
    }

    /**
     * @return Collection<int, Category>
     */
    public function activeCategories(): Collection
    {
        return $this->productRepository->activeCategories();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return Collection<int, Product>
     */
    public function findActiveByIds(array $productIds): Collection
    {
        return $this->productRepository->findActiveByIds($productIds);
    }

    public function findActiveBySlugOrFail(string $slug): Product
    {
        $product = $this->productRepository->findActiveBySlug($slug);

        if (! $product instanceof Product) {
            throw (new ModelNotFoundException)->setModel(Product::class, [$slug]);
        }

        return $product;
    }

    public function findById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }
}
