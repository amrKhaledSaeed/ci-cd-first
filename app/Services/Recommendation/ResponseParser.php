<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Data\Recommendation\RecommendationItemData;
use InvalidArgumentException;
use Smpita\TypeAs\TypeAs;

class ResponseParser
{
    /**
     * @return array<int, RecommendationItemData>
     */
    public function parse(string $rawResponse, int $limit): array
    {
        $decoded = json_decode($this->extractJson($rawResponse), true);

        if (! is_array($decoded) || $this->hasProviderError($decoded)) {
            throw new InvalidArgumentException('Invalid recommendation response shape.');
        }

        $items = $this->normalizedItems($decoded, $limit);

        if ($items === []) {
            throw new InvalidArgumentException('No valid recommendation items could be parsed.');
        }

        return $items;
    }

    private function extractJson(string $rawResponse): string
    {
        $trimmed = mb_trim($rawResponse);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $trimmed) ?? $trimmed;
        }

        return mb_trim($trimmed);
    }

    /**
     * @param  array<int|string, mixed>  $decoded
     */
    private function hasProviderError(array $decoded): bool
    {
        if (! isset($decoded['error']) || ! is_array($decoded['error'])) {
            return false;
        }

        $providerError = TypeAs::nullableString($decoded['error']['message'] ?? null);

        return $providerError !== null && $providerError !== '';
    }

    /**
     * @param  array<int|string, mixed>  $decoded
     * @return array<int, RecommendationItemData>
     */
    private function normalizedItems(array $decoded, int $limit): array
    {
        $items = [];

        foreach (array_slice($decoded, 0, $limit) as $item) {
            if (! is_array($item) || ! isset($item['slug'], $item['name'], $item['price'], $item['reason'])) {
                continue;
            }

            $items[] = RecommendationItemData::fromArray([
                'slug' => $item['slug'],
                'name' => $item['name'],
                'price' => TypeAs::float($item['price']),
                'reason' => $item['reason'],
            ]);
        }

        return $items;
    }
}
