<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id('id_service');
            $table->foreignId('id_aplikasi')->constrained('aplikasi', 'id_aplikasi')->onDelete('cascade');
            $table->string('nama');
            $table->string('tipe_service')->nullable();
            $table->string('ip_local')->nullable();
            $table->string('url_service')->nullable();
            $table->string('url_repository')->nullable();
            $table->string('url_api_docs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
