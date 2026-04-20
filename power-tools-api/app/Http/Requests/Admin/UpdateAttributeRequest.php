<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute')?->id ?? $this->route('attribute');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes', 'slug')->ignore($attributeId)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('attributes', 'code')->ignore($attributeId)],
            'type' => ['required', 'string', Rule::in(['text', 'number', 'boolean', 'select'])],
            'is_filterable' => ['nullable', 'boolean'],
            'is_variant' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên thuộc tính.',
            'name.string' => 'Tên thuộc tính không hợp lệ.',
            'name.min' => 'Tên thuộc tính phải có ít nhất 2 ký tự.',
            'name.max' => 'Tên thuộc tính không được vượt quá 255 ký tự.',

            'slug.string' => 'Slug không hợp lệ.',
            'slug.max' => 'Slug không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',

            'code.string' => 'Mã thuộc tính không hợp lệ.',
            'code.max' => 'Mã thuộc tính không được vượt quá 255 ký tự.',
            'code.unique' => 'Mã thuộc tính đã tồn tại.',

            'type.required' => 'Vui lòng chọn kiểu thuộc tính.',
            'type.in' => 'Kiểu thuộc tính không hợp lệ.',

            'is_filterable.boolean' => 'Trạng thái lọc không hợp lệ.',
            'is_variant.boolean' => 'Trạng thái biến thể không hợp lệ.',
            'status.boolean' => 'Trạng thái hoạt động không hợp lệ.',

            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min' => 'Thứ tự sắp xếp không được nhỏ hơn 0.',

            'description.string' => 'Mô tả không hợp lệ.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = is_string($this->name) ? trim($this->name) : $this->name;
        $slug = is_string($this->slug) ? trim($this->slug) : null;
        $code = is_string($this->code) ? trim($this->code) : null;

        $this->merge([
            'name' => $name,
            'slug' => filled($slug) ? Str::slug($slug) : (filled($name) ? Str::slug($name) : null),
            'code' => filled($code) ? Str::slug($code) : (filled($name) ? Str::slug($name) : null),
            'is_filterable' => filter_var($this->is_filterable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'is_variant' => filter_var($this->is_variant, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }
}