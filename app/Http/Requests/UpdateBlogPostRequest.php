<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('post')?->id;

        return [
            'title_ar'           => ['sometimes', 'string', 'max:255'],
            'title_en'           => ['sometimes', 'string', 'max:255'],
            'slug'               => ['sometimes', 'string', 'max:255',
                                     Rule::unique('blog_posts', 'slug')->ignore($id)],
            'excerpt_ar'         => ['nullable', 'string', 'max:600'],
            'excerpt_en'         => ['nullable', 'string', 'max:600'],
            'content_ar'         => ['nullable', 'string'],
            'content_en'         => ['nullable', 'string'],
            'image'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'category_ids'       => ['nullable', 'array'],
            'category_ids.*'     => ['integer', 'exists:blog_categories,id'],
            'tags'               => ['nullable', 'array'],
            'tags.*'             => ['integer', 'exists:blog_tags,id'],
            'seo_title_ar'       => ['nullable', 'string', 'max:255'],
            'seo_title_en'       => ['nullable', 'string', 'max:255'],
            'seo_description_ar' => ['nullable', 'string', 'max:500'],
            'seo_description_en' => ['nullable', 'string', 'max:500'],
            'is_featured'        => ['nullable', 'boolean'],
            'is_published'       => ['nullable', 'boolean'],
            'allow_comments'     => ['nullable', 'boolean'],
            'published_at'       => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
        if ($this->has('is_published')) {
            $this->merge(['is_published' => $this->boolean('is_published')]);
        }
        if ($this->has('allow_comments')) {
            $this->merge(['allow_comments' => $this->boolean('allow_comments')]);
        }
    }
}
