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
        Schema::create('barber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->comment('0=Sunday, 1=Monday... 6=Saturday');
            $table->time('start_time')->default('08:00');
            $table->time('end_time')->default('19:30');
            $table->boolean('is_off')->default(false);
            $table->timestamps();

            // Đảm bảo không trùng lặp lịch trong cùng 1 ngày của 1 thợ
            $table->unique(['barber_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barber_schedules');
    }
};
