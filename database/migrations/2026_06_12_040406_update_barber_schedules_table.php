<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barber_schedules', 'is_available')) {
            // Drop foreign key constraint trước (nếu có)
            try {
                Schema::table('leave_requests', function (Blueprint $table) {
                    $table->dropForeign(['barber_id']);
                });
            } catch (\Exception $e) {
                // Chưa có FK, bỏ qua
            }

            Schema::table('barber_schedules', function (Blueprint $table) {
                // Drop foreign key constraint trước khi drop index
                $table->dropForeign(['barber_id']);
                
                // Drop unique key cũ (barber_id, day_of_week)
                $table->dropUnique(['barber_id', 'day_of_week']);
                
                $table->boolean('is_available')->default(true)->after('end_time');
                $table->string('reason')->nullable()->after('is_available');
                $table->date('specific_date')->nullable()->after('reason')->comment('Ngày cụ thể, NULL = lặp theo day_of_week');
                
                // Tạo unique key mới bao gồm specific_date
                $table->unique(['barber_id', 'day_of_week', 'specific_date'], 'barber_schedules_unique');
                
                // Thêm lại foreign key
                $table->foreign('barber_id')->references('id')->on('barbers')->onDelete('cascade');
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