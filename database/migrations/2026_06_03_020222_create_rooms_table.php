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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('wp_id')->nullable();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('branch')->default('Hotel');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('regular_price')->nullable();
            $table->string('image')->nullable();
            $table->json('amenities')->nullable();
            $table->string('status')->default('active');
            $table->string('gohost_room_type_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
