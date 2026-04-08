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
        Schema::create('sale_invoice_lines', function (Blueprint $table) {
            $table->id();

            // References
            $table->unsignedBigInteger('sale_invoice_id');
            $table->unsignedBigInteger('product_id')->nullable(); // Allow null if item deleted

            // ===== ITEM SNAPSHOT (Important for history) =====
            $table->string('barcode')->nullable();
            $table->string('item_code');
            $table->string('name');
            $table->string('variant')->nullable();
            $table->longText('description')->nullable();

            // Quantity
            $table->integer('quantity')->default(1);
            $table->string('unit')->nullable();


            $table->string('category_name')->nullable();

            // Pricing snapshot
            $table->decimal('cost', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('sell_price', 15, 2);

            // Discount
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Totals
            $table->decimal('line_amount', 15, 2);
            $table->decimal('vat', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('created_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('sale_invoice_id')
                ->references('id')
                ->on('sale_invoice_headers')
                ->onDelete('cascade');

            // Keep invoice even if item deleted
            $table->foreign('product_id')
                ->references('id')
                ->on('product')   // make sure your table name is exactly "item"
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoice_lines');
    }
};
