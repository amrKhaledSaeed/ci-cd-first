<?php

declare(strict_types=1);

use App\Data\Recommendation\RecommendationContextData;
use App\Models\Product;
use App\Services\Recommendation\RecommendationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

test('recommendation service returns ai recommendations and caches result', function () {
    config([
        'services.ai.provider' => 'openai',
        'services.ai.openai.api_key' => 'test-key',
        'services.ai.openai.model' => 'gpt-test',
        'services.ai.cache_ttl' => 300,
    ]);

    Http::fake([
        'https://api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => json_encode([
                    [
                        'slug' => 'ai-suggested-product',
                        'name' => 'AI Suggested Product',
                        'price' => 39.9,
                        'reason' => 'Great fit with your current item.',
                    ],
                ])]],
            ],
        ], 200),
    ]);

    $product = Product::factory()->create([
        'slug' => 'base-ai-product',
        'category' => 'electronics',
        'is_active' => true,
    ]);

    $service = app(RecommendationService::class);
    $context = RecommendationContextData::fromArray([
        'product' => $product,
        'limit' => 4,
    ]);

    $firstResult = $service->recommend($context);
    $secondResult = $service->recommend($context);

    expect($firstResult->source)->toBe('ai');
    expect($firstResult->provider)->toBe('openai');
    expect($firstResult->items[0]->slug)->toBe('ai-suggested-product');
    expect($secondResult->source)->toBe('ai');

    Http::assertSentCount(1);
});

test('recommendation service falls back on malformed provider output', function () {
    config([
        'services.ai.provider' => 'openai',
        'services.ai.openai.api_key' => 'test-key',
    ]);

    Http::fake([
        'https://api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'invalid-json-output']],
            ],
        ], 200),
    ]);

    $product = Product::factory()->create([
        'slug' => 'base-malformed-product',
        'category' => 'electronics',
        'price' => 120,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'slug' => 'fallback-electronics-product',
        'category' => 'electronics',
        'price' => 130,
        'is_active' => true,
    ]);

    $service = app(RecommendationService::class);
    $result = $service->recommend(RecommendationContextData::fromArray([
        'product' => $product,
        'limit' => 4,
    ]));

    expect($result->source)->toBe('fallback');
    expect($result->provider)->toBeNull();
    expect($result->items)->not->toBeEmpty();
});

test('recommendation service falls back when provider request fails', function () {
    config([
        'services.ai.provider' => 'gemini',
        'services.ai.gemini.api_key' => 'test-key',
        'services.ai.gemini.model' => 'gemini-test',
    ]);

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'error' => ['message' => 'provider unavailable'],
        ], 500),
    ]);

    $product = Product::factory()->create([
        'slug' => 'base-gemini-product',
        'category' => 'sports',
        'price' => 80,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'slug' => 'fallback-sports-product',
        'category' => 'sports',
        'price' => 82,
        'is_active' => true,
    ]);

    $service = app(RecommendationService::class);
    $result = $service->recommend(RecommendationContextData::fromArray([
        'product' => $product,
        'limit' => 4,
    ]));

    expect($result->source)->toBe('fallback');
    expect($result->provider)->toBeNull();
});
