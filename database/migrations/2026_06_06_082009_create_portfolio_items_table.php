<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en');
            $table->text('short_desc_ar')->nullable();
            $table->text('short_desc_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();           // multiple images
            $table->string('client_name')->nullable();
            $table->string('project_url')->nullable();     // live link
            $table->string('case_study_url')->nullable();  // PDF or page
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('portfolio_categories')
                  ->nullOnDelete();
            $table->json('tags')->nullable();              // ["SEO","SEM","PPC"]
            $table->json('results')->nullable();           // [{label_ar, label_en, value}]
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            // SEO
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
    }
};
