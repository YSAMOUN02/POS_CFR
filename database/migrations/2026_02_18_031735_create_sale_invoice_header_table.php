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

            $table->string('customer_id')
                ->nullable();
            $table->string('contact_name')
                ->nullable();
            $table->string('phone')
                ->nullable();
            $table->string('address')
                ->nullable();

            $table->date('invoice_date'); // Date of invoice

            // Calculation
            $table->decimal('total_amount', 15, 2); // Total invoice amount
            $table->decimal('vat_amount', 15, 2)->default(0); // Tax total
            $table->decimal('discount_percent', 12, 2)->default(0); // Discount percentage
            $table->decimal('discount_amount', 15, 2)->default(0); // Discount applied
            $table->decimal('grand_total', 15, 2); // Total invoice amount


            // Remark and Pay
            $table->text('customer_type')->nullable(); // Optional notes
            $table->text('payment_method')->nullable(); // Optional notes

            $table->text('currency_name')->nullable(); // Optional notes
            $table->decimal('factor', 15, 6)->default(1); // conversion rate to main currency (USD)

            $table->text('remarks')->nullable(); // Optional notes
            $table->string('created_by')->nullable();
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoice_headers');
    }
};
