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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('code');
            $table->integer('type'); // fixed
            $table->json('config'); // discount, start_date, end_date, use_limit, user_limit
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('branch_id');

            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
