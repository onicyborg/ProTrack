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
            $table->uuid('supervisor_id')->nullable()->after('weather_notes');
            $table->uuid('executor_id')->nullable()->after('supervisor_id');

            $table->foreign('supervisor_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('executor_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropForeign(['executor_id']);
            $table->dropColumn(['supervisor_id', 'executor_id']);
        });
    }
};
