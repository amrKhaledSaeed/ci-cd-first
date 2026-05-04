<?php

declare(strict_types=1);

namespace App\Data\Recommendation;

use Smpita\TypeAs\TypeAs;

class RecommendationItemData
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly float $price,
        public readonly string $reason,
    ) {}

    /**
     * @param  array{slug: mixed, name: mixed, price: mixed, reason: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: TypeAs::string($data['slug']),
            name: TypeAs::string($data['name']),
            price: TypeAs::float($data['price']),
            reason: TypeAs::string($data['reason']),
        );
    }

    /**
     * @return array{slug: string, name: string, price: float, reason: string}
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => round($this->price, 2),
            'reason' => $this->reason,
        ];
    }
}
