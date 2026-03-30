<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get items
        $sale1 = \App\Models\Sale::where('invoice_no', 'INV-2026-03-30-0001')->first();
        $sale2 = \App\Models\Sale::where('invoice_no', 'INV-2026-03-29-0002')->first();

        if (!$sale1 || !$sale2) {
            $this->command->error('Please seed sales first!');
            return;
        }

        // Get batches
        $batch1 = \App\Models\InventoryBatch::where('batch_no', 'B-2026-02')->first(); // Napa 500, 50 in stock
        $batch2 = \App\Models\InventoryBatch::where('batch_no', 'B-2026-03')->first(); // Napa 650, 80 in stock

        if (!$batch1 || !$batch2) {
            $this->command->error('Please seed inventory batches first!');
            return;
        }

        $saleItems = [
            // Sale 1: 3 strips of Napa 500mg from batch B-2026-02
            [
                'sale_id' => $sale1->id,
                'variant_id' => $batch1->variant_id,
                'batch_id' => $batch1->id,
                'quantity' => 3,
                'unit_price' => $batch1->selling_price, // 10.50
                'total_price' => 3 * $batch1->selling_price,
            ],
            // Sale 2: 2 strips of Napa 650mg from batch B-2026-03
            [
                'sale_id' => $sale2->id,
                'variant_id' => $batch2->variant_id,
                'batch_id' => $batch2->id,
                'quantity' => 2,
                'unit_price' => $batch2->selling_price, // 11.50
                'total_price' => 2 * $batch2->selling_price,
            ],
        ];

        foreach ($saleItems as $item) {
            \App\Models\SaleItem::create($item);
        }
    }
}
