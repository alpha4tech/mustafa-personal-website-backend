<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
  use HasFactory, SoftDeletes;

    protected $fillable = [
        'title_ar',
        'title_en',
        'desc_service_ar',
        'desc_service_en',
        'list_desc_ar',
        'list_desc_en',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'list_desc_ar' => 'array',
        'list_desc_en' => 'array',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];
}

