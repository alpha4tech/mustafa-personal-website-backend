<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blog_post_id',
        'parent_id',
        'name',
        'email',
        'comment',
        'is_approved',
        'ip_address',
    ];

    public function post()
{
    return $this->belongsTo(BlogPost::class, 'blog_post_id');
}

    public function parent()
    {
        return $this->belongsTo(
            BlogComment::class,
            'parent_id'
        );
    }

    public function replies()
    {
        return $this->hasMany(
            BlogComment::class,
            'parent_id'
        );
    }
}
