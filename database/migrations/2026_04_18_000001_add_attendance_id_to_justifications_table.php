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
        Schema::table('justifications', function (Blueprint $table) {
            $table->foreignId('attendance_id')->nullable()->after('student_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('justifications', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');
        });
    }
};
