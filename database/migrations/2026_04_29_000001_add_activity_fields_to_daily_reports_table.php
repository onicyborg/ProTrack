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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->string('kegiatan')->nullable()->after('report_date');
            $table->string('rincian_kegiatan')->nullable()->after('kegiatan');
            $table->string('lokasi_kegiatan')->nullable()->after('rincian_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn(['kegiatan', 'rincian_kegiatan', 'lokasi_kegiatan']);
        });
    }
};
