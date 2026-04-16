<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_monitor', function (Blueprint $table) {
            $table->id('id_log_monitor');
            $table->foreignId('id_aplikasi')->constrained('aplikasi', 'id_aplikasi')->onDelete('cascade');
            $table->foreignId('id_service')->nullable()->constrained('services', 'id_service')->onDelete('cascade');
            $table->string('url')->nullable();
            $table->string('status')->nullable();
            $table->integer('http_status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_monitor');
    }
};
