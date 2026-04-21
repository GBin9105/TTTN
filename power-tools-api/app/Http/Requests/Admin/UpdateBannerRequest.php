<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id ?? $this->route('brand');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brandId)],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên thương hiệu.',
            'slug.unique' => 'Slug thương hiệu đã tồn tại.',
            'logo.image' => 'Logo phải là hình ảnh.',
            'logo.mimes' => 'Logo chỉ chấp nhận jpg, jpeg, png, webp, svg.',
            'logo.max' => 'Dung lượng logo không được vượt quá 2MB.',
            'website.url' => 'Website không đúng định dạng.',
            'status.boolean' => 'Trạng thái không hợp lệ.',
            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = is_string($this->name) ? trim($this->name) : $this->name;
        $slug = is_string($this->slug) ? trim($this->slug) : null;

        $this->merge([
            'name' => $name,
            'slug' => filled($slug) ? Str::slug($slug) : (filled($name) ? Str::slug($name) : null),
            'website' => is_string($this->website) ? trim($this->website) : $this->website,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }
}