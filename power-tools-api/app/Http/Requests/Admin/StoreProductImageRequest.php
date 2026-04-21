<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Vui lòng chọn danh sách ảnh.',
            'images.array' => 'Danh sách ảnh không hợp lệ.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh chỉ chấp nhận jpg, jpeg, png, webp.',
            'images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 4MB.',
        ];
    }
}