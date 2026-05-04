<?php

declare(strict_types=1);

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSimulationRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:card,cod,wallet'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => __('Please provide your full name.'),
            'customer_email.required' => __('Please provide your email address.'),
            'customer_email.email' => __('Please provide a valid email address.'),
            'shipping_address.required' => __('Please provide your shipping address.'),
            'payment_method.required' => __('Please choose a payment method.'),
            'payment_method.in' => __('Please choose a valid payment method.'),
        ];
    }
}
