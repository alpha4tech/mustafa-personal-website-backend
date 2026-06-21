<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title_ar', 'title_en', 'slug',
        'excerpt_ar', 'excerpt_en', 'content_ar', 'content_en',
        'featured_image', 'is_published', 'is_featured', 'allow_comments',
        'views_count', 'published_at', 'seo_title_ar', 'seo_title_en',
        'seo_description_ar', 'seo_description_en',
    ];

    protected $casts = [
        'published_at'   => 'datetime',
        'is_published'   => 'boolean',
        'is_featured'    => 'boolean',
        'allow_comments' => 'boolean',
        'views_count'    => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_category');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }
}
