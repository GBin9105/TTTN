<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
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
            'cancel_reason' => ['required', 'string', 'min:5', 'max:1000'],
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
            'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
            'cancel_reason.string' => 'Lý do hủy đơn hàng không hợp lệ.',
            'cancel_reason.min' => 'Lý do hủy đơn hàng phải có ít nhất 5 ký tự.',
            'cancel_reason.max' => 'Lý do hủy đơn hàng không được vượt quá 1000 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cancel_reason' => is_string($this->cancel_reason) ? trim($this->cancel_reason) : $this->cancel_reason,
        ]);
    }
}