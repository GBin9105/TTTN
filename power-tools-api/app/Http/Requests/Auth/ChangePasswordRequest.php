<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'min:8', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.string' => 'Mật khẩu hiện tại không hợp lệ.',
            'current_password.min' => 'Mật khẩu hiện tại phải có ít nhất 8 ký tự.',
            'current_password.max' => 'Mật khẩu hiện tại không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.string' => 'Mật khẩu mới không hợp lệ.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu mới không được vượt quá 255 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}