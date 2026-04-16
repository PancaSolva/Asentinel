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
        Schema::table('aplikasi', function (Blueprint $table) {
            if (!Schema::hasColumn('aplikasi', 'status')) {
                $table->string('status')->default('active')->after('url_api_docs');
            }
            if (!Schema::hasColumn('aplikasi', 'lastchecked')) {
                $table->timestamp('lastchecked')->nullable()->after('status');
            }
            if (!Schema::hasColumn('aplikasi', 'last_response_time')) {
                $table->integer('last_response_time')->nullable()->after('lastchecked');
            }
            if (!Schema::hasColumn('aplikasi', 'last_status_code')) {
                $table->integer('last_status_code')->nullable()->after('last_response_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->dropColumn(['status', 'lastchecked', 'last_response_time', 'last_status_code']);
        });
    }
};
