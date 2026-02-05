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
        Schema::create('warehouse_product', function (Blueprint $table) {
            $table->id();

            // Link to the correct tables
            $table->foreignId('product_id')->constrained('product')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

            // Quantity
            $table->integer('qty')->default(0);

            // Lot and expiration
            $table->boolean('track_lot')->default(1);
            $table->string('lot')->nullable();
            $table->date('expire')->nullable();

            // Control flag: 1 = active, 0 = expired / blocked
            $table->boolean('control_exp')->default(1);

            $table->timestamps();

            // Make sure each product-warehouse combo is unique

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_product');
    }
};
