<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->header('X-Locale', 'ar');

        return [
            'id'          => $this->id,
            'slug'        => $this->slug,

            // حسب اللغة
            'name'        => $locale === 'ar'
                                ? $this->name_ar
                                : ($this->name_en ?: $this->name_ar),

            // كلا اللغتين للـ admin
            'name_ar'     => $this->name_ar,
            'name_en'     => $this->name_en,

            // 'icon'        => $this->icon,
            // 'color'       => $this->color,
            // 'order'       => $this->order,
            'is_active'   => $this->is_active,

            // عدد المقالات (يظهر فقط عند استخدام withCount)
            'posts_count' => $this->whenCounted('posts'),
        ];
    }
}
