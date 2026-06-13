<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_percent', 5, 2)->default(30.00)->comment('% hoa hồng cho barber theo dịch vụ');
            $table->timestamps();

            $table->unique(['barber_id', 'service_id'], 'commission_barber_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
