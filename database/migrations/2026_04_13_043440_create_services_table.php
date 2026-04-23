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

            // Foreign key to aplikasi table
            $table->foreignId('id_aplikasi')
                ->constrained('aplikasi', 'id_aplikasi')
                ->onDelete('cascade');

            // Service details
            $table->string('nama');
            $table->string('type_service')->nullable();
            $table->string('ip_local')->nullable();
            $table->string('url_service')->nullable();
            $table->string('url_repository')->nullable();
            $table->string('url_api_docs')->nullable();

            // Status info
            $table->string('status')->default('UP');
            $table->timestamp('lastchecked')->nullable();

            // Laravel timestamps (correct way)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};