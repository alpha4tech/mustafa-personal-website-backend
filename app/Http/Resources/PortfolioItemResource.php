<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
 return [
            'id'                  => $this->id,
            'title_ar'            => $this->title_ar,
            'title_en'            => $this->title_en,
            'short_desc_ar'       => $this->short_desc_ar,
            'short_desc_en'       => $this->short_desc_en,
            'content_ar'          => $this->content_ar,
            'content_en'          => $this->content_en,
            'thumbnail'           => $this->thumbnail
                                        ? asset('storage/' . $this->thumbnail)
                                        : null,
            'gallery'             => collect($this->gallery ?? [])->map(
                                        fn($img) => asset('storage/' . $img)
                                     )->values(),
            'client_name'         => $this->client_name,
            'project_url'         => $this->project_url,
            'case_study_url'      => $this->case_study_url,
            'category_id'         => $this->category_id,
            'category'            => new PortfolioCategoryResource($this->whenLoaded('category')),
            'tags'                => $this->tags ?? [],
            'results'             => $this->results ?? [],
            'status'              => $this->status,
            'featured'            => $this->featured,
            'sort_order'          => $this->sort_order,
            'meta_title_ar'       => $this->meta_title_ar,
            'meta_title_en'       => $this->meta_title_en,
            'meta_description_ar' => $this->meta_description_ar,
            'meta_description_en' => $this->meta_description_en,
            'created_at'          => $this->created_at?->toDateTimeString(),
            'updated_at'          => $this->updated_at?->toDateTimeString(),
            'deleted_at'          => $this->deleted_at?->toDateTimeString(),
        ];    }
}
