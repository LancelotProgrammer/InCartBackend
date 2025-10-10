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
        Schema::create('product_file', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('file_id');
            $table->integer('order')->default(1);

            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['product_id', 'file_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_file');
    }
};
