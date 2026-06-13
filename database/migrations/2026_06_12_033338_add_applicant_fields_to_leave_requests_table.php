<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_requests', 'applicant_name')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('applicant_name')->nullable()->after('recipient');
                $table->date('applicant_dob')->nullable()->after('applicant_name');
                $table->string('applicant_address')->nullable()->after('applicant_dob');
                $table->string('applicant_phone')->nullable()->after('applicant_address');
                $table->string('applicant_workplace')->nullable()->after('applicant_phone');
                $table->string('applicant_position')->nullable()->after('applicant_workplace');
                $table->dateTime('start_time')->nullable()->after('end_date');
                $table->dateTime('end_time')->nullable()->after('start_time');
                $table->boolean('commitment')->default(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn([
                'applicant_name',
                'applicant_dob',
                'applicant_address',
                'applicant_phone',
                'applicant_workplace',
                'applicant_position',
                'start_time',
                'end_time',
            ]);
            $table->text('commitment')->nullable()->change();
        });
    }
};