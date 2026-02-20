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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->foreignId('customer_id')->nullable();
            $table->integer('queue_no')->nullable();
            $table->integer('customer_qty')->default(1);
            $table->boolean('is_occupied')->default(false);
            $table->enum('status', ['waiting','preparing','ready','served','paid','cancelled'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
