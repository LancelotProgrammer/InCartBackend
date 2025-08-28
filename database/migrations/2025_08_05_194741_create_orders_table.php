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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->text('notes')->nullable();
            $table->integer('order_status');
            $table->integer('payment_status');
            $table->integer('delivery_status');
            $table->decimal('subtotal_price', 10, 2)->default(0);  // Before discounts, taxes, fees
            $table->decimal('coupon_discount', 10, 2)->default(0); // Discount from coupon
            $table->decimal('delivery_fee', 10, 2)->default(0);    // Calculated by zone/distance
            $table->decimal('service_fee', 10, 2)->default(0);     // Optional
            $table->decimal('tax_amount', 10, 2)->default(0);      // VAT or other tax
            $table->decimal('total_price', 10, 2)->default(0);     // What the customer pays
            $table->timestamp('delivery_date')->nullable();
            $table->string('payment_token')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('user_address_id');

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('cart_id')->references('id')->on('carts')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_address_id')->references('id')->on('user_addresses')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
