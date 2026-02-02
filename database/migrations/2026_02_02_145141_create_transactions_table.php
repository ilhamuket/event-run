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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_category_id')->constrained()->onDelete('cascade');

            // Invoice & Reference
            $table->string('merchant_ref')->unique()->comment('Invoice number from our system');
            $table->string('tripay_reference')->nullable()->comment('Reference from Tripay');

            // Payment Info
            $table->string('payment_method')->default('QRIS');
            $table->string('payment_name')->nullable();

            // Amount
            $table->unsignedBigInteger('amount')->comment('Base amount');
            $table->unsignedBigInteger('fee')->default(0)->comment('Admin fee');
            $table->unsignedBigInteger('total_amount')->comment('Amount + Fee');

            // QRIS Data
            $table->text('qr_string')->nullable();
            $table->string('qr_url')->nullable();

            // Status
            $table->enum('status', ['UNPAID', 'PAID', 'EXPIRED', 'FAILED', 'REFUND'])->default('UNPAID');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            // Additional
            $table->string('checkout_url')->nullable();
            $table->json('tripay_response')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('tripay_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
