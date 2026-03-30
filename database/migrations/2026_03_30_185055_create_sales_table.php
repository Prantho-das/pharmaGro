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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique()->notNullable();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('sub_total', 15, 2)->notNullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->notNullable();
            $table->decimal('paid_amount', 15, 2)->notNullable();
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->string('payment_method', 50)->default('cash');
            $table->foreignId('sold_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Index for invoice lookups
            $table->index('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
