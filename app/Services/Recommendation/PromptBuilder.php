<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Data\Recommendation\RecommendationContextData;
use App\Models\Product;
use Smpita\TypeAs\TypeAs;

class PromptBuilder
{
    public function build(RecommendationContextData $context): string
    {
        $product = $context->product;
        $cartLines = $this->cartSummary($context->cartLines);

        return <<<PROMPT
You are a strict recommendation engine for an e-commerce app.
Return JSON only.
Return an array with at most {$context->limit} items.
Each item must contain exactly:
- slug (string)
- name (string)
- price (number)
- reason (string, max 120 chars)
Do not include inactive or out-of-stock suggestions.

Current product context:
- slug: {$product->slug}
- name: {$product->name}
- category: {$product->category}
- price: {$product->price}
- description: {$product->description}
- stock: {$product->stock}

Cart summary:
{$cartLines}
PROMPT;
    }

    /**
     * @param  array<int, Product>  $viewedProducts
     * @param  array<int, Product>  $candidateProducts
     */
    public function buildFromViewedProducts(array $viewedProducts, array $candidateProducts, int $limit): string
    {
        $viewedSummary = $this->productSummary($viewedProducts);
        $candidateSummary = $this->candidateSummary($candidateProducts);

        return <<<PROMPT
You are a strict recommendation engine for an e-commerce app.
Return JSON only.
Return exactly {$limit} items when possible.
Each item must contain exactly:
- slug (string)
- name (string)
- price (number)
- reason (string, max 120 chars)
Pick only products from the provided candidate list.

Last viewed products:
{$viewedSummary}

Candidate products:
{$candidateSummary}
PROMPT;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cartLines
     */
    private function cartSummary(array $cartLines): string
    {
        if ($cartLines === []) {
            return '- cart is empty';
        }

        $summary = [];

        foreach ($cartLines as $line) {
            $summary[] = sprintf(
                '- %s (qty: %d, unit_price: %.2f)',
                TypeAs::string($line['name'] ?? 'unknown'),
                TypeAs::int($line['quantity'] ?? 0),
                TypeAs::float($line['unit_price'] ?? 0),
            );
        }

        return implode(PHP_EOL, $summary);
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function productSummary(array $products): string
    {
        if ($products === []) {
            return '- none';
        }

        $summary = [];

        foreach ($products as $product) {
            $summary[] = sprintf(
                '- %s | %s | %.2f | %s',
                TypeAs::string($product->name),
                TypeAs::string($product->category),
                TypeAs::float($product->price),
                TypeAs::string($product->description),
            );
        }

        return implode(PHP_EOL, $summary);
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function candidateSummary(array $products): string
    {
        if ($products === []) {
            return '- none';
        }

        $summary = [];

        foreach ($products as $product) {
            $summary[] = sprintf(
                '- slug=%s | name=%s | category=%s | price=%.2f',
                TypeAs::string($product->slug),
                TypeAs::string($product->name),
                TypeAs::string($product->category),
                TypeAs::float($product->price),
            );
        }

        return implode(PHP_EOL, $summary);
    }
}
