<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_requests', 'leave_type')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('leave_type')->default('full_day')->after('status')->comment('full_day, morning, afternoon, evening, custom');
                $table->json('leave_dates')->nullable()->after('leave_type')->comment('Mảng các ngày nghỉ cụ thể');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['leave_type', 'leave_dates']);
        });
    }
};