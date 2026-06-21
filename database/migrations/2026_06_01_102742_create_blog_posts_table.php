<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
      {

                    Schema::create('blog_posts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->string('title_ar');
                    $table->string('title_en');

                    $table->string('slug')->unique();

                    $table->text('excerpt_ar')->nullable();
                    $table->text('excerpt_en')->nullable();

                    $table->longText('content_ar');
                    $table->longText('content_en');

                    $table->string('featured_image')->nullable();

                    $table->enum('status', [
                        'draft',
                        'published',
                        'archived'
                    ])->default('draft');

                    $table->unsignedBigInteger('views')->default(0);

                    $table->timestamp('published_at')->nullable();

                    $table->string('seo_title_ar')->nullable();
                    $table->string('seo_title_en')->nullable();

                    $table->text('seo_description_ar')->nullable();
                    $table->text('seo_description_en')->nullable();

                    $table->softDeletes();

                    $table->timestamps();
                });

     }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
