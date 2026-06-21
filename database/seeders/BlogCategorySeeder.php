<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogCategory;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        BlogCategory::insert([
            [
                'name_ar' => 'التسويق',
                'name_en' => 'Marketing',
                'slug' => 'marketing',
                'is_active' => 1
            ],
            [
                'name_ar' => 'العلامة التجارية',
                'name_en' => 'Branding',
                'slug' => 'branding',
                'is_active' => 1
            ],
            [
                'name_ar' => 'السوشال ميديا',
                'name_en' => 'Social Media',
                'slug' => 'social-media',
                'is_active' => 1
            ],
            [
                'name_ar' => 'لينكدإن',
                'name_en' => 'LinkedIn',
                'slug' => 'linkedin',
                'is_active' => 1
            ],
            [
                'name_ar' => 'السيرة الذاتية',
                'name_en' => 'CV Writing',
                'slug' => 'cv-writing',
                'is_active' => 1
            ],
            [
                'name_ar' => 'التعليم والتدريب',
                'name_en' => 'Education',
                'slug' => 'education',
                'is_active' => 1
            ],
        ]);
    }
}
