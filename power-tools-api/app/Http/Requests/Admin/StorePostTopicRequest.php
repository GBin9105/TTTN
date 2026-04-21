<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('post_topics', 'slug')],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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