<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'string', 'max:255', 'unique:blog_tags,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'الاسم العربي مطلوب',
            'name_en.required' => 'English name is required',
            'slug.unique'      => 'هذا الـ Slug مستخدم مسبقاً',
        ];
    }
}
