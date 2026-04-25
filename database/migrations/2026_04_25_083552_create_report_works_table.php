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
        Schema::create('report_works', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('daily_report_id');
            $table->uuid('task_id');
            $table->decimal('volume', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('daily_report_id')->references('id')->on('daily_reports')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_works');
    }
};
