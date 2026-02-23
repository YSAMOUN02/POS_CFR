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
        Schema::create('sale_invoice_headers', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('invoice_number')->unique(); // Invoice number

       $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete(); // if customer deleted → set null

          


            $table->date('invoice_date'); // Date of invoice
            $table->date('due_date')->nullable(); // Payment due date
            $table->decimal('total_amount', 15, 2); // Total invoice amount
            $table->decimal('vat_amount', 15, 2)->default(0); // Tax total
            $table->decimal('discount_amount', 15, 2)->default(0); // Discount applied
            $table->decimal('return_amount', 15, 2)->default(0); // Discount applied

            $table->text('payment_method')->nullable(); // Optional notes
            $table->text('remarks')->nullable(); // Optional notes
            $table->timestamps(); // created_at & updated_at


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoice_header');
    }
};
