<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Data\Product\ProductListData;
use App\Data\Recommendation\RecommendationContextData;
use App\Models\Product;
use App\Models\User;
use App\Services\Analytics\StoreAnalyticsService;
use App\Services\Product\ProductService;
use App\Services\Recommendation\RecommendationService;
use App\Services\Wishlist\WishlistService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Smpita\TypeAs\TypeAs;

class StorefrontService
{
    public function __construct(
        private ProductService $productService,
        private RecommendationService $recommendationService,
        private WishlistService $wishlistService,
        private StoreAnalyticsService $storeAnalyticsService,
    ) {}

    /**
     * @return array{products: LengthAwarePaginator<int, Product>, filters: array{search: string|null, category_id: int|null, sort: string, per_page: int}, categories: \Illuminate\Support\Collection<int, \App\Models\Category>, wishlistProductIds: array<int, int>}
     */
    public function listing(ProductListData $listingData, ?User $user): array
    {
        $products = $this->productService->listActiveProducts($listingData);

        return [
            'products' => $products,
            'filters' => $listingData->toArray(),
            'categories' => $this->productService->activeCategories(),
            'wishlistProductIds' => $this->wishlistIdsForVisibleProducts($products, $user),
        ];
    }

    /**
     * @return array{products: LengthAwarePaginator<int, Product>, filters: array{search: string|null, category_id: int|null, sort: string, per_page: int}, categories: \Illuminate\Support\Collection<int, \App\Models\Category>, recommendations: array<string, mixed>, wishlistProductIds: array<int, int>}
     */
    public function home(ProductListData $listingData, ?User $user): array
    {
        $listing = $this->listing($listingData, $user);

        /** @var array<int, int|string> $viewedProductIds */
        $viewedProductIds = TypeAs::array(session('store.viewed_products', []), default: []);
        $normalizedViewedIds = array_map(fn (int|string $id): int => TypeAs::int($id), $viewedProductIds);
        $viewedProducts = $this->productService->findActiveByIds($normalizedViewedIds)
            ->sortBy(fn (Product $product): int => array_search($product->id, $normalizedViewedIds, true) ?: 0)
            ->values();

        $listing['recommendations'] = $this->recommendationService
            ->recommendFromViewedProducts($viewedProducts->take(3)->all(), 3)
            ->toArray();

        return $listing;
    }

    /**
     * @return array{product: Product, recommendations: array<string, mixed>, isWishlisted: bool}
     */
    public function productDetail(string $slug, ?User $user): array
    {
        $activeProduct = $this->productService->findActiveBySlugOrFail($slug);

        $recommendationResult = $this->recommendationService->recommend(
            RecommendationContextData::fromArray([
                'product' => $activeProduct,
                'limit' => 4,
            ]),
        );

        $this->storeAnalyticsService->track(
            eventName: 'product_view',
            user: $user,
            product: $activeProduct,
        );

        $this->rememberViewedProduct($activeProduct->id);

        return [
            'product' => $activeProduct,
            'recommendations' => $recommendationResult->toArray(),
            'isWishlisted' => $user instanceof User
                ? $this->wishlistService->contains($user, $activeProduct)
                : false,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Product>  $products
     * @return array<int, int>
     */
    private function wishlistIdsForVisibleProducts(LengthAwarePaginator $products, ?User $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        /** @var array<int, int> $visibleProductIds */
        $visibleProductIds = collect($products->items())
            ->pluck('id')
            ->map(fn (mixed $id): int => TypeAs::int($id))
            ->all();

        return $this->wishlistService->productIdsFor($user, $visibleProductIds);
    }

    private function rememberViewedProduct(int $productId): void
    {
        /** @var array<int, mixed> $rawViewed */
        $rawViewed = TypeAs::array(session('store.viewed_products', []), default: []);

        /** @var array<int, int> $viewed */
        $viewed = collect($rawViewed)
            ->map(fn (mixed $id): int => TypeAs::int($id))
            ->prepend($productId)
            ->unique()
            ->take(3)
            ->values()
            ->all();

        session()->put('store.viewed_products', $viewed);
    }
}
