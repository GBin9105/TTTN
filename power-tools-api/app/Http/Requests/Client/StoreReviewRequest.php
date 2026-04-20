<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_item_id.required' => 'Vui lòng chọn sản phẩm cần đánh giá.',
            'order_item_id.integer' => 'Sản phẩm đánh giá không hợp lệ.',
            'order_item_id.exists' => 'Sản phẩm đánh giá không tồn tại.',

            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá không hợp lệ.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5.',

            'title.string' => 'Tiêu đề đánh giá không hợp lệ.',
            'title.max' => 'Tiêu đề đánh giá không được vượt quá 255 ký tự.',

            'content.string' => 'Nội dung đánh giá không hợp lệ.',
            'content.max' => 'Nội dung đánh giá không được vượt quá 2000 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_item_id' => is_numeric($this->order_item_id) ? (int) $this->order_item_id : $this->order_item_id,
            'rating' => is_numeric($this->rating) ? (int) $this->rating : $this->rating,
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'content' => is_string($this->content) ? trim($this->content) : $this->content,
        ]);
    }
}