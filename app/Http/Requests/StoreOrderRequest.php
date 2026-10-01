<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'customer.name' => ['required', 'string', 'max:120'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'customer.city' => ['required', 'string', 'max:100'],
            'customer.address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['mtn', 'moov', 'celtiis', 'cod'])],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_slug' => ['required', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.size' => ['nullable', 'string', 'max:30'],
            'items.*.color' => ['nullable', 'string', 'max:80'],
            'items.*.bundle_id' => ['nullable', Rule::in(['complete-look-10'])],
        ];
    }
}
