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
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'last_response_time')) {
                $table->integer('last_response_time')->nullable();
            }
            if (!Schema::hasColumn('services', 'last_status_code')) {
                $table->integer('last_status_code')->nullable();
            }
            // Use lastchecked as the primary monitoring timestamp to avoid redundancy
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['last_response_time', 'last_status_code']);
        });
    }
};
