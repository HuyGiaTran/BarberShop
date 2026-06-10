<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('appointment_time_tmp')->nullable()->after('appointment_date');
        });

        DB::table('appointments')
            ->select(['id', 'appointment_time'])
            ->orderBy('id')
            ->get()
            ->each(function (object $appointment): void {
                $value = $appointment->appointment_time;

                if ($value !== null && strlen($value) === 5) {
                    $value .= ':00';
                }

                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['appointment_time_tmp' => $value]);
            });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('appointment_time');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('appointment_time_tmp', 'appointment_time');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_time_tmp')->nullable()->after('appointment_date');
        });

        DB::table('appointments')
            ->select(['id', 'appointment_time'])
            ->orderBy('id')
            ->get()
            ->each(function (object $appointment): void {
                $value = $appointment->appointment_time;

                if ($value !== null && strlen($value) >= 5) {
                    $value = substr($value, 0, 5);
                }

                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['appointment_time_tmp' => $value]);
            });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('appointment_time');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('appointment_time_tmp', 'appointment_time');
        });
    }
};
