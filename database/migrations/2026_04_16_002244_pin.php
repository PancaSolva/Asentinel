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
        Schema::create('pin', function (Blueprint $table) {
            $table->id('id_pinned');
            $table->string('id_user')->constrained('users', 'id_user')->onDelete('cascade');
            $table->foreignId('id_aplikasi')->constrained('aplikasi', 'id_aplikasi')->onDelete('cascade');
            $table->timestamps('created_at', 'updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin');
    }
};
