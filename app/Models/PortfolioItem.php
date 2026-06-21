<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
class PortfolioItem extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'title_ar',
        'title_en',
        'short_desc_ar',
        'short_desc_en',
        'content_ar',
        'content_en',
        'thumbnail',
        'gallery',
        'client_name',
        'project_url',
        'case_study_url',
        'category_id',
        'tags',
        'results',
        'status',
        'featured',
        'sort_order',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
    ];

    protected $casts = [
        'gallery'  => 'array',
        'tags'     => 'array',
        'results'  => 'array',
        'featured' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

}
