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
        Schema::create('item_ledger_entries', function (Blueprint $table) {
            $table->id();

            // document tracing
            $table->string('entry_no')->unique()->nullable();
            $table->date('posting_date');

            $table->string('document_type');
            // PURCHASE, SALE, SERVICE, EXPENSE, TRANSFER, ADJUSTMENT, RETURN_SALE, RETURN_PURCHASE
            $table->string('source_no')->nullable();
            $table->string('document_no')->index()->nullable();

            // source links
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_table')->nullable();
            // sale_invoice_lines, purchase_lines, expenses, etc.

            // item
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('barcode')->nullable();
            $table->string('item_code')->nullable()->index();
            $table->string('name');
            $table->string('variant')->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->string('category_name')->nullable();
            $table->enum('type', ['product', 'service'])->nullable();
            // warehouse / lot
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->string('warehouse_name')->nullable();
            $table->string('lot')->nullable()->index();
            $table->date('expire_date')->nullable();

            // quantities
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('remaining_quantity', 15, 4)->default(0);
            // useful for incoming entries like purchase lots

            $table->enum('entry_type', ['positive', 'negative'])->index();
            // positive = stock in, negative = stock out

            // cost / price / value
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0)->nullable();  // price set on product with discount and vat
            $table->decimal('sell_price', 15, 4)->default(0)->nullable(); //actualt selling price, can be different from unit_price due to discounts or promotions
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);
            $table->decimal('vat', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 4)->default(0);

            $table->decimal('line_amount', 15, 4)->default(0);  //raw selling value  || grpss
            $table->decimal('net_amount', 15, 4)->default(0);   //after discount , after discount  👉 before VAT

            $table->decimal('grand_total_amount', 15, 4)->default(0); // final amount customer pays.

            // total movement value, usually net or costing value depending on your policy

            // business parties
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_name')->nullable(); //general customer if no specific customer
            $table->string('customer_phone')->nullable(); //general customer if no specific customer
            $table->string('customer_address')->nullable(); //general customer if no specific customer
            $table->unsignedBigInteger('vendor_id')->nullable()->index();

            // extra
            $table->string('payment_method')->nullable();
            $table->text('remark')->nullable();
            $table->string('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_ledger_entries');
    }
};
