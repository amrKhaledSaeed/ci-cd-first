<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Smpita\TypeAs\TypeAs;

class CartQuantityWithinStock implements ValidationRule
{
    public function __construct(private int $productId) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $quantity = TypeAs::int($value);

        if ($quantity < 1) {
            return;
        }

        $product = Product::query()
            ->whereKey($this->productId)
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            $fail(__('The selected product is unavailable.'));

            return;
        }

        if ($quantity > $product->stock) {
            $fail(__('Requested quantity exceeds available stock.'));
        }
    }
}
