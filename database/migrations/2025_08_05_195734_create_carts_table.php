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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->timestamps();

            $table->unsignedBigInteger('order_id')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};


// ALTER TABLE `carts` MODIFY `order_id` bigint unsigned null;
// ALTER TABLE `carts` ADD COLUMN `order_number` varchar(255) NULL AFTER `id`;
// UPDATE `carts` SET `order_number` = CONCAT( 'ORD-', id, '-', LPAD(FLOOR(RAND(id) * 1000000), 6, '0') ) WHERE `order_number` IS NULL;
// ALTER TABLE `carts` MODIFY `order_number` varchar(255) NOT NULL;