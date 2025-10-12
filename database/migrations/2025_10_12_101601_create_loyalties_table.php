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
        Schema::create('loyalties', function (Blueprint $table) {
            $table->id();
            $table->integer('points')->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->timestamps();

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalties');
    }
};
