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
        $table->id();

          $table->unsignedBigInteger('sale_order_id')->nullable();
        
        $table->string('source_no')->nullable();
        $table->string('invoice_number')->unique();

        $table->string('customer_id')->nullable();
        $table->string('contact_name')->nullable();
        $table->string('phone')->nullable();
        $table->string('address')->nullable();

        $table->date('invoice_date');

        $table->decimal('total_amount', 15, 2)->default(0);
        $table->decimal('vat_amount', 15, 2)->default(0);
        $table->decimal('discount_percent', 12, 2)->default(0);
        $table->decimal('discount_amount', 15, 2)->default(0);
        $table->decimal('grand_total', 15, 2)->default(0);

        $table->text('customer_type')->nullable();
        $table->text('payment_method')->nullable();

        $table->text('currency_name')->nullable();
        $table->decimal('factor', 15, 6)->default(1);

        $table->text('remarks')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
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
