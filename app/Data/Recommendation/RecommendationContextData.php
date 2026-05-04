<?php

declare(strict_types=1);

namespace App\Data\Recommendation;

use App\Models\Product;
use Smpita\TypeAs\TypeAs;

class RecommendationContextData
{
    /**
     * @param  array<int, array<string, mixed>>  $cartLines
     */
    public function __construct(
        public readonly Product $product,
        public readonly array $cartLines = [],
        public readonly int $limit = 4,
    ) {}

    /**
     * @param  array{product: Product, cart_lines?: mixed, limit?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<int, array<string, mixed>> $cartLines */
        $cartLines = TypeAs::array($data['cart_lines'] ?? [], default: []);

        return new self(
            product: $data['product'],
            cartLines: $cartLines,
            limit: TypeAs::int($data['limit'] ?? 4),
        );
    }
}
