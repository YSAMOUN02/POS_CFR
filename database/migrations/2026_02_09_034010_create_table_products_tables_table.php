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
        Schema::create('table_products_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('restaurant_tables')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('delivery_note')->nullable();

            // start from 1 every day
            $table->string('queue_no') ->default(1);

            $table->integer('qty')->default(0);

            // trak how many order from customer
            $table->integer('order_qty')->default(0);

            // track new order
            $table->integer('printed_qty')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat', 5, 2)->default(0);
            $table->decimal('gross_amount', 10, 2)->default(0); // price * qty
            $table->decimal('discount_amount', 10, 2)->default(0); // (gross * discount %)
            $table->decimal('net_amount', 10, 2)->default(0); // gross - discount + vat

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_products_tables');
    }
};
