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
        Schema::create('web_guests', function (Blueprint $table) {
            $table->id('premission_id');

            $table->foreignId('id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('id_aplikasi')
                ->constrained('aplikasi')
                ->onDelete('cascade')->nullable();
            $table->foreignId('id_service')
                ->constrained('services', 'id_service')
                ->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
