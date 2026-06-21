<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/* ═══════════════════════════════════════════
   Store
═══════════════════════════════════════════ */
class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'title_ar'            => 'required|string|max:255',
        'title_en'            => 'required|string|max:255',
        'slug'                => 'nullable|string|unique:blog_posts,slug',
        'excerpt_ar'          => 'nullable|string|max:500',
        'excerpt_en'          => 'nullable|string|max:500',
        'content_ar'          => 'required|string',
        'content_en'          => 'required|string',
        'seo_title_ar'        => 'nullable|string|max:255',
        'seo_title_en'        => 'nullable|string|max:255',
        'seo_description_ar'  => 'nullable|string|max:500',
        'seo_description_en'  => 'nullable|string|max:500',
        'is_published'        => 'boolean',
        'is_featured'         => 'boolean',
        'allow_comments'      => 'boolean',
        'published_at'        => 'nullable|date',
        'featured_image'      => 'nullable|image|max:2048',
         'category_ids'       => ['nullable', 'array'],
         'category_ids.*'     => ['integer', 'exists:blog_categories,id'],
        // 'category_ids'        => 'nullable|array',
        // 'category_ids.*'      => 'integer|exists:blog_categories,id',
        'tags'                => 'nullable|array',
        'tags.*'              => 'integer|exists:blog_tags,id',
    ];
}

    public function messages(): array
    {
        return [
            'title_ar.required'    => 'العنوان بالعربية مطلوب',
            'title_en.required'    => 'العنوان بالإنجليزية مطلوب',
            'excerpt_ar.required'  => 'الملخص بالعربية مطلوب',
            'excerpt_en.required'  => 'الملخص بالإنجليزية مطلوب',
            'category_ids.required' => 'يرجى اختيار تصنيف',
            'featured_image.image'          => 'يجب أن يكون الملف صورة',
             'category_ids.*.exists'  => 'التصنيف المختار غير موجود',
            'tags.*.exists'          => 'الوسم المختار غير موجود',
            'featured_image.max'            => 'حجم الصورة لا يتجاوز 3 ميغابايت',
            'featured_image.mimes'          => 'صيغة الصورة يجب أن تكون: jpg, png, webp',
            'slug.unique'          => 'هذا الـ Slug مستخدم مسبقاً، اختر آخر',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured'  => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
        ]);
    }
}

