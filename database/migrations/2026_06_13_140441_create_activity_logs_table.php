<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);          // blue | green | amber | red | purple
            $table->string('icon', 50);           // bi-pencil | bi-envelope ...
            $table->string('action');             // نص الحدث (HTML مسموح)
            $table->string('subject_type')->nullable(); // App\Models\BlogPost
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); }
};
