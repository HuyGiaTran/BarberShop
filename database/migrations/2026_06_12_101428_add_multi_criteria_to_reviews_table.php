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
        Schema::table('reviews', function (Blueprint $table) {
            $table->tinyInteger('space_rating')->nullable()->after('rating');
            $table->tinyInteger('staff_rating')->nullable()->after('space_rating');
            $table->tinyInteger('service_rating')->nullable()->after('staff_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['space_rating', 'staff_rating', 'service_rating']);
        });
    }
};
