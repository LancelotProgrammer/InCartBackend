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
        Schema::create('order_archives', function (Blueprint $table) {
            $table->id();
            $table->timestamp('archived_at');

            $table->string('order_number');
            $table->string('cancel_reason')->nullable();
            $table->text('notes')->nullable();

            $table->integer('order_status');
            $table->integer('payment_status');
            $table->integer('delivery_status');

            $table->decimal('subtotal_price', 10, 2)->default(0);  // Before discounts, taxes, fees
            $table->decimal('discount_price', 10, 2)->default(0);  // Discount from coupon
            $table->decimal('delivery_fee', 10, 2)->default(0);    // Calculated by zone/distance
            $table->decimal('service_fee', 10, 2)->default(0);     // Optional
            $table->decimal('tax_amount', 10, 2)->default(0);      // VAT or other tax
            $table->decimal('total_price', 10, 2)->default(0);     // What the customer pays

            $table->integer('delivery_scheduled_type');
            $table->timestamp('delivery_date')->nullable();

            $table->string('payment_token')->nullable();
            $table->timestamps();

            $table->json('customer');
            $table->json('delivery')->nullable();
            $table->json('manager')->nullable();
            $table->json('branch');
            $table->json('coupon')->nullable();
            $table->json('payment_method');
            $table->json('user_address');
            $table->json('cart');
            $table->json('audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_archives');
    }
};
