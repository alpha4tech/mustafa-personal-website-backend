<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioItemRequest extends FormRequest
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
            'short_desc_ar'       => 'nullable|string|max:500',
            'short_desc_en'       => 'nullable|string|max:500',
            'content_ar'          => 'nullable|string',
            'content_en'          => 'nullable|string',
            'thumbnail'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'gallery.*'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'client_name'         => 'nullable|string|max:255',
            'project_url'         => 'nullable|url|max:500',
            'case_study_url'      => 'nullable|url|max:500',
            'category_id'         => 'nullable|exists:portfolio_categories,id',
            'tags'                => 'nullable|array',
            'tags.*'              => 'string|max:100',
            'results'             => 'nullable|array',
            'results.*.label_ar'  => 'required_with:results|string|max:100',
            'results.*.label_en'  => 'required_with:results|string|max:100',
            'results.*.value'     => 'required_with:results|string|max:100',
            'status'              => 'required|in:published,draft,archived',
            'featured'            => 'boolean',
            'sort_order'          => 'integer|min:0',
            'meta_title_ar'       => 'nullable|string|max:255',
            'meta_title_en'       => 'nullable|string|max:255',
            'meta_description_ar' => 'nullable|string|max:500',
            'meta_description_en' => 'nullable|string|max:500',
            'remove_thumbnail'    => 'boolean',
            'remove_gallery'      => 'nullable|array',
            'remove_gallery.*'    => 'string',
        ];
    }
}
