<?php

declare(strict_types=1);

namespace App\Data\Product;

use Smpita\TypeAs\TypeAs;

class ProductListData
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?int $categoryId,
        public readonly string $sort,
        public readonly int $perPage,
    ) {}

    /**
     * @param  array{search?: mixed, category_id?: mixed, sort?: mixed, per_page?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        $sort = TypeAs::string($data['sort'] ?? 'relevance');
        $allowedSort = ['relevance', 'price_asc', 'price_desc', 'newest'];

        return new self(
            search: TypeAs::nullableString($data['search'] ?? null),
            categoryId: TypeAs::nullableInt($data['category_id'] ?? null),
            sort: in_array($sort, $allowedSort, true) ? $sort : 'relevance',
            perPage: TypeAs::int($data['per_page'] ?? 12),
        );
    }

    /**
     * @return array{search: string|null, category_id: int|null, sort: string, per_page: int}
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'category_id' => $this->categoryId,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
        ];
    }
}
