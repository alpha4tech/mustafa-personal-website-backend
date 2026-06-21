<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogTag;

class BlogTagSeeder extends Seeder
{
    public function run(): void
    {
        BlogTag::insert([
            ['name_ar' => 'SEO', 'name_en' => 'SEO'],
            ['name_ar' => 'Marketing', 'name_en' => 'Marketing'],
            ['name_ar' => 'Content', 'name_en' => 'Content'],
            ['name_ar' => 'Branding', 'name_en' => 'Branding'],
            ['name_ar' => 'LinkedIn', 'name_en' => 'LinkedIn'],
            ['name_ar' => 'CV', 'name_en' => 'CV'],
            ['name_ar' => 'مواقع التواصل الاجتماعي', 'name_en' => 'Social Media'],
        ]);
    }
}
