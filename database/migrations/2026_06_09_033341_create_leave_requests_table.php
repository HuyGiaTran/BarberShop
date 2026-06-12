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
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barber_id')->constrained('barbers')->onDelete('cascade');
                $table->string('recipient')->default('Ban Giám Đốc');
                $table->string('applicant_name');
                $table->date('applicant_dob')->nullable();
                $table->string('applicant_address')->nullable();
                $table->string('applicant_phone')->nullable();
                $table->string('applicant_workplace')->nullable();
                $table->string('applicant_position')->nullable();
                $table->dateTime('start_time');
                $table->dateTime('end_time');
                $table->text('reason');
                $table->string('handover_person')->nullable();
                $table->boolean('commitment')->default(true);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
