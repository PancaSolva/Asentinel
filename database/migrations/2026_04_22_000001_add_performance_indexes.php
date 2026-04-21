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
        Schema::table('log_monitor', function (Blueprint $table) {
            $table->index('checked_at');
            $table->index(['id_aplikasi', 'id_service']);
        });

        Schema::table('log_anomali', function (Blueprint $table) {
            $table->index(['id_aplikasi', 'id_service']);
            $table->index('detected_at');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('id_aplikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_monitor', function (Blueprint $table) {
            $table->dropIndex(['checked_at']);
            $table->dropIndex(['id_aplikasi', 'id_service']);
        });

        Schema::table('log_anomali', function (Blueprint $table) {
            $table->dropIndex(['id_aplikasi', 'id_service']);
            $table->dropIndex(['detected_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['id_aplikasi']);
        });
    }
};
