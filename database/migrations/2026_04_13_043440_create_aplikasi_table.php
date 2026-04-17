<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplikasi', function (Blueprint $table) {
            $table->id('id_aplikasi');
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('tipe')->nullable();
            $table->string('ip_local')->nullable();
            $table->string('url_service')->nullable();
            $table->string('url_repository')->nullable();
            $table->string('url_api_docs')->nullable();
            $table->integer('interval')->default(20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplikasi');
    }
};
