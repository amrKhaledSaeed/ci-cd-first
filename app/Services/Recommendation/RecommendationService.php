<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Data\Recommendation\RecommendationContextData;
use App\Data\Recommendation\RecommendationItemData;
use App\Data\Recommendation\RecommendationResultData;
use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use App\Services\Recommendation\Contracts\RecommendationProviderContract;
use App\Services\Recommendation\Contracts\RecommendationServiceContract;
use App\Services\Recommendation\Providers\ClaudeRecommendationProvider;
use App\Services\Recommendation\Providers\GeminiRecommendationProvider;
use App\Services\Recommendation\Providers\OpenAiRecommendationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Smpita\TypeAs\TypeAs;
use Throwable;

class RecommendationService extends RecommendationServiceContract
{
    public function __construct(
        private ProductRepository $productRepository,
        private PromptBuilder $promptBuilder,
        private ResponseParser $responseParser,
        private OpenAiRecommendationProvider $openAiRecommendationProvider,
        private GeminiRecommendationProvider $geminiRecommendationProvider,
        private ClaudeRecommendationProvider $claudeRecommendationProvider,
    ) {}

    public function recommend(RecommendationContextData $context): RecommendationResultData
    {
        $providerName = TypeAs::string(config('services.ai.provider', 'openai'));
        $cacheKey = $this->cacheKey($providerName, $context);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return RecommendationResultData::fromArray($cached);
        }

        $startedAt = microtime(true);
        $fallbackUsed = false;
        $result = null;

        try {
            if (! $this->hasProviderCredentials($providerName)) {
                throw new RuntimeException('AI provider credentials are missing.');
            }
            $provider = $this->resolveProvider($providerName);
            $prompt = $this->promptBuilder->build($context);
            $rawResponse = $provider->generate($prompt);
            $items = $this->responseParser->parse($rawResponse, $context->limit);
            $result = new RecommendationResultData(
                items: $items,
                source: 'ai',
                provider: $provider->name(),
                generatedAt: now()->toIso8601String(),
            );
        } catch (Throwable $exception) {
            $fallbackUsed = true;
            $result = $this->fallbackRecommendations($context);

            Log::warning('recommendation.provider_failed', [
                'provider' => $providerName,
                'error' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
        Log::info('recommendation.completed', [
            'provider' => $providerName,
            'latency_ms' => $latencyMs,
            'fallback_used' => $fallbackUsed,
            'product_id' => $context->product->id,
        ]);

        $ttlSeconds = TypeAs::int(config('services.ai.cache_ttl', 300));
        Cache::put($cacheKey, $result->toArray(), now()->addSeconds($ttlSeconds));

        return $result;
    }

    /**
     * @param  array<int, Product>  $viewedProducts
     */
    public function recommendFromViewedProducts(array $viewedProducts, int $limit = 3): RecommendationResultData
    {
        $providerName = TypeAs::string(config('services.ai.provider', 'openai'));
        $viewedProductIds = array_values(array_unique(array_map(
            fn (Product $product): int => TypeAs::int($product->id),
            $viewedProducts,
        )));
        $cacheKey = sprintf(
            'recommendations:home:%s:%s:%d',
            $providerName,
            md5(json_encode($viewedProductIds) ?: '[]'),
            $limit,
        );

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return RecommendationResultData::fromArray($cached);
        }

        if ($viewedProducts === []) {
            $fallbackResult = $this->randomFallbackRecommendations($limit);
            $this->cacheRecommendationResult($cacheKey, $fallbackResult);

            return $fallbackResult;
        }

        $startedAt = microtime(true);
        $fallbackUsed = false;

        try {
            $result = $this->buildHomeAiResult($providerName, $viewedProducts, $viewedProductIds, $limit);
        } catch (Throwable $exception) {
            $fallbackUsed = true;
            $result = $this->randomFallbackRecommendations($limit);

            Log::warning('recommendation.home_provider_failed', [
                'provider' => $providerName,
                'error' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
        Log::info('recommendation.home_completed', [
            'provider' => $providerName,
            'latency_ms' => $latencyMs,
            'fallback_used' => $fallbackUsed,
            'viewed_product_ids' => $viewedProductIds,
        ]);

        $this->cacheRecommendationResult($cacheKey, $result);

        return $result;
    }

    private function resolveProvider(string $provider): RecommendationProviderContract
    {
        if ($provider === 'openai') {
            return $this->openAiRecommendationProvider;
        }

        if ($provider === 'gemini') {
            return $this->geminiRecommendationProvider;
        }

        if ($provider === 'claude') {
            return $this->claudeRecommendationProvider;
        }

        return $this->openAiRecommendationProvider;
    }

    private function cacheKey(string $providerName, RecommendationContextData $context): string
    {
        $cartHashSource = json_encode($context->cartLines);
        $hashableCart = is_string($cartHashSource) ? $cartHashSource : '[]';

        return sprintf(
            'recommendations:%s:%d:%s:%d',
            $providerName,
            $context->product->id,
            md5($hashableCart),
            $context->limit,
        );
    }

    private function fallbackRecommendations(RecommendationContextData $context): RecommendationResultData
    {
        $products = $this->productRepository->findFallbackRecommendations($context->product, $context->limit);

        $items = $products
            ->map(callback: fn (Product $product): RecommendationItemData => new RecommendationItemData(
                slug: TypeAs::string($product->slug),
                name: TypeAs::string($product->name),
                price: TypeAs::float($product->price),
                reason: __('Similar category and price range.'),
            ))
            ->values()
            ->all();

        return new RecommendationResultData(
            items: $items,
            source: 'fallback',
            provider: null,
            generatedAt: now()->toIso8601String(),
        );
    }

    private function randomFallbackRecommendations(int $limit): RecommendationResultData
    {
        $products = $this->productRepository->randomActive($limit);

        /** @var array<int, RecommendationItemData> $items */
        $items = $products
            ->map(callback: fn (Product $product): RecommendationItemData => new RecommendationItemData(
                slug: TypeAs::string($product->slug),
                name: TypeAs::string($product->name),
                price: TypeAs::float($product->price),
                reason: __('Recommended from popular in-stock items.'),
            ))
            ->values()
            ->all();

        return new RecommendationResultData(
            items: $items,
            source: 'fallback',
            provider: null,
            generatedAt: now()->toIso8601String(),
        );
    }

    /**
     * @param  array<int, Product>  $viewedProducts
     * @param  array<int, int>  $viewedProductIds
     */
    private function buildHomeAiResult(
        string $providerName,
        array $viewedProducts,
        array $viewedProductIds,
        int $limit,
    ): RecommendationResultData {
        if (! $this->hasProviderCredentials($providerName)) {
            throw new RuntimeException('AI provider credentials are missing.');
        }

        $provider = $this->resolveProvider($providerName);
        $candidateProducts = $this->productRepository
            ->recommendationCandidatePool(limit: 24, excludeIds: $viewedProductIds)
            ->all();
        $prompt = $this->promptBuilder->buildFromViewedProducts(
            viewedProducts: $viewedProducts,
            candidateProducts: $candidateProducts,
            limit: $limit,
        );
        $rawResponse = $provider->generate($prompt);
        $parsedItems = $this->responseParser->parse($rawResponse, $limit);
        $matchedProducts = $this->matchedActiveProductsForParsedItems($parsedItems);
        $items = $this->buildMatchedItems($matchedProducts, $parsedItems, $limit);

        if ($items === []) {
            throw new RuntimeException('No valid recommendation items matched active products.');
        }

        $items = $this->appendFillerItems($items, $matchedProducts, $limit);

        return new RecommendationResultData(
            items: array_slice($items, 0, $limit),
            source: 'ai',
            provider: $provider->name(),
            generatedAt: now()->toIso8601String(),
        );
    }

    /**
     * @param  array<int, RecommendationItemData>  $parsedItems
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    private function matchedActiveProductsForParsedItems(array $parsedItems): \Illuminate\Database\Eloquent\Collection
    {
        $slugs = collect($parsedItems)
            ->pluck('slug')
            ->map(fn (mixed $slug): string => TypeAs::string($slug))
            ->all();

        return $this->productRepository->findActiveBySlugs(array_values($slugs));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Product>  $matchedProducts
     * @param  array<int, RecommendationItemData>  $parsedItems
     * @return array<int, RecommendationItemData>
     */
    private function buildMatchedItems(
        \Illuminate\Database\Eloquent\Collection $matchedProducts,
        array $parsedItems,
        int $limit,
    ): array {
        $parsedBySlug = collect($parsedItems)->keyBy('slug');

        /** @var array<int, RecommendationItemData> $items */
        $items = $matchedProducts
            ->map(function (Product $product) use ($parsedBySlug): RecommendationItemData {
                $parsedItem = $parsedBySlug->get($product->slug);
                $reason = $parsedItem instanceof RecommendationItemData
                    ? $parsedItem->reason
                    : __('Based on your recently viewed products.');

                return new RecommendationItemData(
                    slug: TypeAs::string($product->slug),
                    name: TypeAs::string($product->name),
                    price: TypeAs::float($product->price),
                    reason: $reason,
                );
            })
            ->take($limit)
            ->values()
            ->all();

        return $items;
    }

    /**
     * @param  array<int, RecommendationItemData>  $items
     * @param  \Illuminate\Database\Eloquent\Collection<int, Product>  $matchedProducts
     * @return array<int, RecommendationItemData>
     */
    private function appendFillerItems(
        array $items,
        \Illuminate\Database\Eloquent\Collection $matchedProducts,
        int $limit,
    ): array {
        if (count($items) >= $limit) {
            return $items;
        }

        /** @var array<int, int> $excludeIds */
        $excludeIds = array_values($matchedProducts->pluck('id')->map(
            fn (mixed $id): int => TypeAs::int($id),
        )->all());
        $fillerProducts = $this->productRepository->randomActive(
            limit: $limit - count($items),
            excludeIds: $excludeIds,
        );

        foreach ($fillerProducts as $fillerProduct) {
            $items[] = new RecommendationItemData(
                slug: TypeAs::string($fillerProduct->slug),
                name: TypeAs::string($fillerProduct->name),
                price: TypeAs::float($fillerProduct->price),
                reason: __('Popular fallback recommendation.'),
            );
        }

        return $items;
    }

    private function cacheRecommendationResult(string $cacheKey, RecommendationResultData $result): void
    {
        Cache::put(
            $cacheKey,
            $result->toArray(),
            now()->addSeconds(TypeAs::int(config('services.ai.cache_ttl', 300))),
        );
    }

    private function hasProviderCredentials(string $provider): bool
    {
        if ($provider === 'openai') {
            return TypeAs::string(config('services.ai.openai.api_key', '')) !== '';
        }

        if ($provider === 'gemini') {
            return TypeAs::string(config('services.ai.gemini.api_key', '')) !== '';
        }

        if ($provider === 'claude') {
            return TypeAs::string(config('services.ai.claude.api_key', '')) !== '';
        }

        return false;
    }
}
