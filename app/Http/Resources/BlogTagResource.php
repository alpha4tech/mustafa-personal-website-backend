<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->header('X-Locale', 'ar');

        return [
            'id'     => $this->id,
            'slug'   => $this->slug,

            // حسب اللغة مع fallback
            'name'   => $locale === 'ar'
                            ? $this->name_ar
                            : ($this->name_en ?: $this->name_ar),

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
        ];
    }
}
