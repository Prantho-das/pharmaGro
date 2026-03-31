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
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->integer('points')->notNullable(); // positive for earned, negative for redeemed
            $table->enum('type', ['earned', 'redeemed', 'adjusted'])->default('earned');
            $table->integer('balance_after')->notNullable(); // points balance after this transaction
            $table->string('notes')->nullable();
            $table->timestamps();

            // Indexes for queries
            $table->index(['customer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
