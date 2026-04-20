<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncProductAttributesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attributes' => ['required', 'array', 'min:1'],

            'attributes.*.attribute_id' => [
                'required',
                'integer',
                Rule::exists('attributes', 'id'),
            ],

            'attributes.*.value' => ['nullable', 'string', 'max:255'],
            'attributes.*.numeric_value' => ['nullable', 'numeric'],
            'attributes.*.boolean_value' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'attributes.required' => 'Vui lòng gửi danh sách thuộc tính.',
            'attributes.array' => 'Danh sách thuộc tính không hợp lệ.',
            'attributes.min' => 'Phải có ít nhất 1 thuộc tính.',

            'attributes.*.attribute_id.required' => 'Thiếu mã thuộc tính.',
            'attributes.*.attribute_id.integer' => 'Mã thuộc tính không hợp lệ.',
            'attributes.*.attribute_id.exists' => 'Thuộc tính không tồn tại.',

            'attributes.*.value.string' => 'Giá trị thuộc tính dạng chữ không hợp lệ.',
            'attributes.*.value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',

            'attributes.*.numeric_value.numeric' => 'Giá trị số không hợp lệ.',
            'attributes.*.boolean_value.boolean' => 'Giá trị đúng/sai không hợp lệ.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $attributes = $this->attributes;

        if (! is_array($attributes)) {
            return;
        }

        $normalized = array_map(function ($item) {
            if (! is_array($item)) {
                return $item;
            }

            return [
                'attribute_id' => $item['attribute_id'] ?? null,
                'value' => isset($item['value']) && is_string($item['value'])
                    ? trim($item['value'])
                    : ($item['value'] ?? null),
                'numeric_value' => $item['numeric_value'] ?? null,
                'boolean_value' => array_key_exists('boolean_value', $item)
                    ? filter_var($item['boolean_value'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                    : null,
            ];
        }, $attributes);

        $this->merge([
            'attributes' => $normalized,
        ]);
    }
}