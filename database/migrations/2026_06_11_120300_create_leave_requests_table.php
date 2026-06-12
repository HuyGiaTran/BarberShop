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
        if (! Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barber_id')->constrained()->onDelete('cascade');
                $table->string('recipient')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason')->nullable();
                $table->string('handover_person')->nullable();
                $table->boolean('commitment')->default(true);
                $table->text('rejection_reason')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });

            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'start_date')) {
                $table->date('start_date')->nullable()->after('recipient');
            }

            if (! Schema::hasColumn('leave_requests', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            if (! Schema::hasColumn('leave_requests', 'handover_person')) {
                $table->string('handover_person')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('leave_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('commitment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
