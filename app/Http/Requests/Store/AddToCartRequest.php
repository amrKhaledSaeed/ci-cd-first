<?php

declare(strict_types=1);

namespace App\Http\Requests\Store;

use App\Models\Product;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Smpita\TypeAs\TypeAs;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true)),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $productId = TypeAs::int($this->input('product_id'));
                    $quantity = TypeAs::int($value);
                    $product = Product::query()->find($productId);

                    if ($product === null || ! $product->is_active) {
                        $fail(__('The selected product is unavailable.'));

                        return;
                    }

                    if ($quantity > $product->stock) {
                        $fail(__('Requested quantity exceeds available stock.'));
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => __('Please select a product.'),
            'product_id.exists' => __('The selected product is unavailable.'),
            'quantity.required' => __('Please select a quantity.'),
            'quantity.min' => __('Quantity must be at least 1.'),
        ];
    }
}
