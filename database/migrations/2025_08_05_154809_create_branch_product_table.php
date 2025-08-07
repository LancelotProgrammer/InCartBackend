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
        Schema::create('branch_product', function (Blueprint $table) {
            $table->id();

            $table->decimal('price');
            $table->integer('unit');
            $table->integer('discount');
            $table->integer('maximum_order_quantity');
            $table->integer('minimum_order_quantity');
            $table->double('quantity');
            $table->dateTime('expires_at');
            $table->dateTime('published_at')->nullable();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['product_id', 'product_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product');
    }
};
