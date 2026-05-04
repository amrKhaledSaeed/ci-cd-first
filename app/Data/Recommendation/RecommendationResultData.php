<?php

declare(strict_types=1);

namespace App\Data\Recommendation;

use Smpita\TypeAs\TypeAs;

class RecommendationResultData
{
    /**
     * @param  array<int, RecommendationItemData>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly string $source,
        public readonly ?string $provider,
        public readonly string $generatedAt,
    ) {}

    /**
     * @param  array{items?: mixed, source?: mixed, provider?: mixed, generated_at?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<int, mixed> $itemsData */
        $itemsData = is_array($data['items'] ?? null) ? $data['items'] : [];
        $items = [];

        foreach ($itemsData as $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[] = RecommendationItemData::fromArray([
                'slug' => TypeAs::string($item['slug'] ?? ''),
                'name' => TypeAs::string($item['name'] ?? ''),
                'price' => TypeAs::float($item['price'] ?? 0),
                'reason' => TypeAs::string($item['reason'] ?? ''),
            ]);
        }

        return new self(
            items: $items,
            source: is_string($data['source'] ?? null) ? $data['source'] : 'fallback',
            provider: is_string($data['provider'] ?? null) ? $data['provider'] : null,
            generatedAt: is_string($data['generated_at'] ?? null) ? $data['generated_at'] : now()->toIso8601String(),
        );
    }

    /**
     * @return array{items: array<int, array{slug: string, name: string, price: float, reason: string}>, source: string, provider: string|null, generated_at: string}
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                fn (RecommendationItemData $item): array => $item->toArray(),
                $this->items,
            ),
            'source' => $this->source,
            'provider' => $this->provider,
            'generated_at' => $this->generatedAt,
        ];
    }
}
