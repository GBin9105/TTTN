<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'min:2', 'max:255'],
            'customer_email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'customer_phone' => ['required', 'string', 'min:9', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
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
            'customer_name.required' => 'Vui lòng nhập tên người nhận.',
            'customer_name.string' => 'Tên người nhận không hợp lệ.',
            'customer_name.min' => 'Tên người nhận phải có ít nhất 2 ký tự.',
            'customer_name.max' => 'Tên người nhận không được vượt quá 255 ký tự.',

            'customer_email.email' => 'Email người nhận không đúng định dạng.',
            'customer_email.max' => 'Email người nhận không được vượt quá 255 ký tự.',

            'customer_phone.required' => 'Vui lòng nhập số điện thoại người nhận.',
            'customer_phone.string' => 'Số điện thoại người nhận không hợp lệ.',
            'customer_phone.min' => 'Số điện thoại phải có ít nhất 9 ký tự.',
            'customer_phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'shipping_address.string' => 'Địa chỉ giao hàng không hợp lệ.',
            'shipping_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự.',

            'note.string' => 'Ghi chú không hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',

            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',

            'shipping_fee.numeric' => 'Phí vận chuyển phải là số.',
            'shipping_fee.min' => 'Phí vận chuyển không được nhỏ hơn 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => is_string($this->customer_name) ? trim($this->customer_name) : $this->customer_name,
            'customer_email' => is_string($this->customer_email) ? trim(mb_strtolower($this->customer_email)) : $this->customer_email,
            'customer_phone' => is_string($this->customer_phone) ? trim($this->customer_phone) : $this->customer_phone,
            'shipping_address' => is_string($this->shipping_address) ? trim($this->shipping_address) : $this->shipping_address,
            'note' => is_string($this->note) ? trim($this->note) : $this->note,
            'shipping_fee' => is_numeric($this->shipping_fee) ? (float) $this->shipping_fee : ($this->shipping_fee ?? 0),
        ]);
    }
}