<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('status');
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->boolean('allow_comments')->default(true)->after('is_featured');
            $table->unsignedInteger('views_count')->default(0)->after('views');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'is_featured', 'allow_comments', 'views_count']);
        });
    }
};
