<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã giảm giá');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->comment('percent/fixed');
            $table->decimal('discount_value', 10, 2)->default(0)->comment('Giá trị giảm');
            $table->decimal('min_order_amount', 10, 2)->nullable()->comment('Đơn tối thiểu');
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Giảm tối đa (cho percent)');
            $table->unsignedInteger('usage_limit')->default(0)->comment('Số lần dùng tối đa, 0=không giới hạn');
            $table->unsignedInteger('used_count')->default(0)->comment('Số lần đã dùng');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
