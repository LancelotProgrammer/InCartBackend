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
        Schema::create('advertisement_file', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('advertisement_id');
            $table->unsignedBigInteger('file_id');
            $table->integer('order')->default(1);

            $table->foreign('advertisement_id')->references('id')->on('advertisements')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['advertisement_id', 'file_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_file');
    }
};
