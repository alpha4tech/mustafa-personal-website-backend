<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PortfolioCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name_ar', 'name_en', 'slug'];

    public function items()
    {
        return $this->hasMany(PortfolioItem::class, 'category_id');
    }
}
