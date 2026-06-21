<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/* ═══════════════════════════════════════════
   Store Category
═══════════════════════════════════════════ */
class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_ar'   => ['required', 'string', 'max:100'],
            'name_en'   => ['required', 'string', 'max:100'],
            'slug'      => ['required', 'string', 'max:100', 'unique:blog_categories,slug'],
            // 'icon'      => ['nullable', 'string', 'max:10'],   // emoji
            // 'color'     => ['nullable', 'string', 'max:7'],    // #162FBB
            // 'order'     => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم التصنيف بالعربية مطلوب',
            'name_en.required' => 'اسم التصنيف بالإنجليزية مطلوب',
            'slug.required'    => 'الـ Slug مطلوب',
            'slug.unique'      => 'هذا الـ Slug موجود مسبقاً',
        ];
    }
}


/* ═══════════════════════════════════════════
   Update Category
═══════════════════════════════════════════ */
class UpdateBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return [
            'name_ar'   => ['sometimes', 'string', 'max:100'],
            'name_en'   => ['sometimes', 'string', 'max:100'],
            'slug'      => ['sometimes', 'string', 'max:100',
                            Rule::unique('blog_categories', 'slug')->ignore($id)],
            // 'icon'      => ['nullable', 'string', 'max:10'],
            // 'color'     => ['nullable', 'string', 'max:7'],
            // 'order'     => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}


/* ═══════════════════════════════════════════
   Store Tag
═══════════════════════════════════════════ */
class StoreBlogTagRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:50'],
            'name_en' => ['required', 'string', 'max:50'],
            'slug'    => ['required', 'string', 'max:50', 'unique:blog_tags,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم الوسم بالعربية مطلوب',
            'name_en.required' => 'اسم الوسم بالإنجليزية مطلوب',
            'slug.required'    => 'الـ Slug مطلوب',
            'slug.unique'      => 'هذا الوسم موجود مسبقاً',
        ];
    }
}
