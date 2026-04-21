<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), Rule::notIn([$categoryId])],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'parent_id.not_in' => 'Danh mục cha không hợp lệ.',
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'slug.unique' => 'Slug danh mục đã tồn tại.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = is_string($this->name) ? trim($this->name) : $this->name;
        $slug = is_string($this->slug) ? trim($this->slug) : null;

        $this->merge([
            'name' => $name,
            'slug' => filled($slug) ? Str::slug($slug) : (filled($name) ? Str::slug($name) : null),
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }
}