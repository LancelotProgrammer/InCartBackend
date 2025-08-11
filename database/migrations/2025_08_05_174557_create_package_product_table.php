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
        Schema::create('package_product', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('package_id')->references('id')->on('packages')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['package_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_product');
    }
};
