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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('restrict');
            $table->foreignId('batch_id')->constrained('inventory_batches')->onDelete('restrict');
            $table->integer('quantity')->notNullable();
            $table->decimal('unit_price', 15, 2)->notNullable();
            $table->decimal('total_price', 15, 2)->notNullable();
            $table->timestamps();

            // Composite index for batch-based queries
            $table->index(['batch_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
