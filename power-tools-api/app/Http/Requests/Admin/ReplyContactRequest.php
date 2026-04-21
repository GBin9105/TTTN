<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReplyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply_content' => ['required', 'string', 'min:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'reply_content.required' => 'Vui lòng nhập nội dung phản hồi.',
            'reply_content.min' => 'Nội dung phản hồi phải có ít nhất 2 ký tự.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reply_content' => is_string($this->reply_content) ? trim($this->reply_content) : $this->reply_content,
        ]);
    }
}