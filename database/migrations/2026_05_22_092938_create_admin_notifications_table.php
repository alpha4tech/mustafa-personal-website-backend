<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
public function up(): void
{
    Schema::create('admin_notifications', function (Blueprint $table) {
        $table->id();
        $table->string('type');         // 'contact' | 'low_stock' | 'expiry_warning' | 'expired'
        $table->string('title');
        $table->text('message');
        $table->string('icon')->nullable();
        $table->string('color')->nullable();  // 'danger' | 'warning' | 'info'
        $table->unsignedBigInteger('reference_id')->nullable();   // contact_id أو medicine_id
        $table->string('reference_type')->nullable();  // 'contact' | 'medicine'
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
