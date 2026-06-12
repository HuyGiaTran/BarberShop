<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barber_schedules', 'is_available')) {
            Schema::table('barber_schedules', function (Blueprint $table) {
                // Drop unique key cũ (barber_id, day_of_week)
                $table->dropUnique(['barber_id', 'day_of_week']);
                
                $table->boolean('is_available')->default(true)->after('end_time');
                $table->string('reason')->nullable()->after('is_available');
                $table->date('specific_date')->nullable()->after('reason')->comment('Ngày cụ thể, NULL = lặp theo day_of_week');
                
                // Tạo unique key mới bao gồm specific_date
                $table->unique(['barber_id', 'day_of_week', 'specific_date'], 'barber_schedules_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('barber_schedules', function (Blueprint $table) {
            $table->dropIndex('barber_schedules_unique');
            $table->dropColumn(['is_available', 'reason', 'specific_date']);
            $table->unique(['barber_id', 'day_of_week']);
        });
    }
};