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
            if (! Schema::hasColumn('appointments', 'booking_reference')) {
                $table->string('booking_reference')->nullable()->after('service_id');
                $table->unsignedInteger('booking_sequence')->default(1)->after('booking_reference');
                $table->boolean('is_booking_primary')->default(true)->after('booking_sequence');
                $table->decimal('deposit_amount', 10, 2)->default(50000)->after('notes');
                $table->string('deposit_status')->default('unpaid')->after('deposit_amount');
                $table->timestamp('deposit_paid_at')->nullable()->after('deposit_status');
                $table->string('deposit_transaction_id')->nullable()->after('deposit_paid_at');
            }
        });

        DB::table('appointments')
            ->select('id')
            ->whereNull('booking_reference')
            ->orderBy('id')
            ->get()
            ->each(function (object $appointment): void {
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update([
                        'booking_reference' => 'APT-'.$appointment->id,
                        'booking_sequence' => 1,
                        'is_booking_primary' => true,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $columns = [
                'booking_reference',
                'booking_sequence',
                'is_booking_primary',
                'deposit_amount',
                'deposit_status',
                'deposit_paid_at',
                'deposit_transaction_id',
            ];

            $existingColumns = array_filter($columns, fn (string $column): bool => Schema::hasColumn('appointments', $column));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
