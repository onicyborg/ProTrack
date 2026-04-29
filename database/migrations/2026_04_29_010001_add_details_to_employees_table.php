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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('position');
            $table->string('phone_number')->nullable()->after('nik');
            $table->date('birth_date')->nullable()->after('phone_number');
            $table->string('gender')->nullable()->after('birth_date');
            $table->text('address')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nik', 'phone_number', 'birth_date', 'gender', 'address']);
        });
    }
};
