<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'min:9', 'max:20', 'required_without:email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
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
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên không hợp lệ.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'email.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'phone.required_without' => 'Vui lòng nhập số điện thoại hoặc email.',
            'phone.string' => 'Số điện thoại không hợp lệ.',
            'phone.min' => 'Số điện thoại phải có ít nhất 9 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'subject.string' => 'Tiêu đề không hợp lệ.',
            'subject.max' => 'Tiêu đề không được vượt quá 255 ký tự.',

            'message.required' => 'Vui lòng nhập nội dung liên hệ.',
            'message.string' => 'Nội dung liên hệ không hợp lệ.',
            'message.min' => 'Nội dung liên hệ phải có ít nhất 10 ký tự.',
            'message.max' => 'Nội dung liên hệ không được vượt quá 2000 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? trim(mb_strtolower($this->email)) : $this->email,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'subject' => is_string($this->subject) ? trim($this->subject) : $this->subject,
            'message' => is_string($this->message) ? trim($this->message) : $this->message,
        ]);
    }
}