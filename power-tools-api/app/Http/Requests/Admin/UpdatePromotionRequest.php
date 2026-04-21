<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promotionId = $this->route('promotion')?->id ?? $this->route('promotion');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('promotions', 'code')->ignore($promotionId)],
            'type' => ['required', 'string', Rule::in(['fixed_amount', 'percentage', 'fixed_price'])],
            'value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', Rule::exists('products', 'id')],
        ];
    }
}