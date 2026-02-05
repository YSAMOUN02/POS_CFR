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
      Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('customer_code')->unique()->nullable(); // CUS0001
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // POS related
            $table->enum('type', ['walk_in', 'member', 'vip'])->default('walk_in');
            $table->decimal('credit_limit', 12, 2)->default(0);  //Allow pay later
            $table->decimal('balance', 12, 2)->default(0); // owe or advance
            $table->integer('point')->default(0); // loyalty point

            // Status
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
