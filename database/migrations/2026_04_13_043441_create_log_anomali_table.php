<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_anomali', function (Blueprint $table) {
            $table->id('id_log_anomali'); // The ERD says id_log_monitor but it's clearly a different table
            $table->foreignId('id_aplikasi')->constrained('aplikasi', 'id_aplikasi')->onDelete('cascade');
            $table->foreignId('id_service')->constrained('services', 'id_service')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('severity')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_anomali');
    }
};
