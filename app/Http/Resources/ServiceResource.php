<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            return [
            'id'              => $this->id,
            'title_ar'        => $this->title_ar,
            'title_en'        => $this->title_en,
            'desc_service_ar' => $this->desc_service_ar,
            'desc_service_en' => $this->desc_service_en,
            'list_desc_ar'    => $this->list_desc_ar ?? [],
            'list_desc_en'    => $this->list_desc_en ?? [],
            'icon'            => $this->icon,
            'sort_order'      => $this->sort_order,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
