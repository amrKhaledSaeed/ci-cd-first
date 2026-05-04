<?php

declare(strict_types=1);

namespace App\Http\Requests\Store;

use App\Rules\CartQuantityWithinStock;
use Illuminate\Foundation\Http\FormRequest;
use Smpita\TypeAs\TypeAs;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => [
                'required',
                'integer',
                'min:0',
                new CartQuantityWithinStock(TypeAs::int($this->route('productId'))),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => __('Please provide a quantity.'),
            'quantity.integer' => __('Quantity must be a number.'),
            'quantity.min' => __('Quantity cannot be negative.'),
        ];
    }
}
