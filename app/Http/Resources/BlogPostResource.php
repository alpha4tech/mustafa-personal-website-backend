<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->header('X-Locale', 'ar');

        return [
            'id'   => $this->id,
            'slug' => $this->slug,

            // ── حقول اللغة الحالية (للواجهة العامة) ──
            'title'   => $locale === 'ar' ? $this->title_ar   : $this->title_en,
            'excerpt' => $locale === 'ar' ? $this->excerpt_ar : $this->excerpt_en,
            'content' => $locale === 'ar' ? $this->content_ar : $this->content_en,

            // ── حقول ثنائية اللغة ── (✅ بدون شرط isAdmin)
            'title_ar'   => $this->title_ar,
            'title_en'   => $this->title_en,
            'excerpt_ar' => $this->excerpt_ar,
            'excerpt_en' => $this->excerpt_en,
            'content_ar' => $this->content_ar,   // ✅ كان: when($isAdmin, ...)
            'content_en' => $this->content_en,   // ✅ كان: when($isAdmin, ...)

            // ── SEO ──
            'seo_title_ar'       => $this->seo_title_ar,
            'seo_title_en'       => $this->seo_title_en,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_description_en' => $this->seo_description_en,

            // ── الصورة ──
            'featured_image' => $this->featured_image
                ? asset('storage/' . $this->featured_image)
                : null,
            'thumbnail' => $this->featured_image
                ? asset('storage/' . $this->featured_image)
                : null,

            // ── العلاقات ──
            'categories' => BlogCategoryResource::collection($this->whenLoaded('categories')),
            'category'   => $this->whenLoaded('categories',
                fn() => $this->categories->first()
                    ? new BlogCategoryResource($this->categories->first())
                    : null
            ),
            'tags' => BlogTagResource::collection($this->whenLoaded('tags')),

            // ── IDs للـ edit form ── (✅ بدون شرط isAdmin)
            'category_ids' => $this->relationLoaded('categories')
                ? $this->categories->pluck('id')->toArray()
                : [],
            'tag_ids' => $this->relationLoaded('tags')
                ? $this->tags->pluck('id')->toArray()
                : [],

            // ── الحالة ── (✅ بدون شرط isAdmin)
            'status'         => $this->is_published ? 'published' : 'draft',
            'is_published'   => $this->is_published,   // ✅ كان: when($isAdmin, ...)
            'is_featured'    => $this->is_featured,
            'allow_comments' => $this->allow_comments, // ✅ كان: when($isAdmin, ...)

            // ── الإحصائيات ──
            'views_count' => $this->views_count ?? 0,
            'views'       => $this->views_count ?? 0,
            'read_time'   => $this->read_time   ?? null,

            // ── التواريخ ──
            'published_at' => $this->published_at?->format('Y-m-d\TH:i'),
            'created_at'   => $this->created_at?->format('Y-m-d\TH:i'),
            'updated_at'   => $this->updated_at?->format('Y-m-d\TH:i'),
            'date'         => $this->published_at?->locale($locale)->translatedFormat('j F Y'),
            'date_iso'     => $this->published_at?->toDateString(),
        ];
    }
}
