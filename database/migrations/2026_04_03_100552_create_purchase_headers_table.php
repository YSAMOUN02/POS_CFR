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
        Schema::create('purchase_headers', function (Blueprint $table) {
            $table->id();
            $table->string('no')->unique();
            $table->unsignedBigInteger('vendor_id');

            // ✅ changed here
            $table->date('posting_date');
            

            $table->string('payment_method')->nullable();
            $table->text('remark')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_headers');
    }
};
