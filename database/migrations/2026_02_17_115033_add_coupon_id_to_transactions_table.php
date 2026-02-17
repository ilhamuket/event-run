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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('event_coupon_id')->nullable()->after('event_category_id')->constrained('event_coupons')->nullOnDelete();
            $table->unsignedInteger('discount_amount')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['event_coupon_id']);
            $table->dropColumn(['event_coupon_id', 'discount_amount']);
        });
    }
};
