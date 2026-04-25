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
            $table->unsignedBigInteger('id_aplikasi')->nullable();
        
            $table->foreign('id_aplikasi')
                ->references('id_aplikasi')
                ->on('aplikasi')
                ->onDelete('cascade');
            
            $table->timestamps();
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
