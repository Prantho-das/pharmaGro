<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get variants
        $napa500 = \App\Models\ProductVariant::where('sku', 'NAPA-500-STR')->first();
        $napa650 = \App\Models\ProductVariant::where('sku', 'NAPA-650-STR')->first();
        $monas20 = \App\Models\ProductVariant::where('sku', 'MONAS-20-STR')->first();
        $monas40 = \App\Models\ProductVariant::where('sku', 'MONAS-40-STR')->first();

        if (!$napa500 || !$napa650 || !$monas20 || !$monas40) {
            $this->command->error('Please seed product variants first!');
            return;
        }

        $batches = [
            // Napa 500mg batches (FEFO test)
            [
                'variant_id' => $napa500->id,
                'batch_no' => 'B-2026-01',
                'expiry_date' => '2027-12-31',
                'purchase_price' => 8.00,
                'selling_price' => 10.00,
                'current_stock' => 100,
                'is_active' => true,
            ],
            [
                'variant_id' => $napa500->id,
                'batch_no' => 'B-2026-02',
                'expiry_date' => '2026-06-30',
                'purchase_price' => 8.50,
                'selling_price' => 10.50,
                'current_stock' => 50,
                'is_active' => true,
            ],
            // Napa 650mg batches
            [
                'variant_id' => $napa650->id,
                'batch_no' => 'B-2026-03',
                'expiry_date' => '2027-03-15',
                'purchase_price' => 9.00,
                'selling_price' => 11.50,
                'current_stock' => 80,
                'is_active' => true,
            ],
            // Monas 20mg batches
            [
                'variant_id' => $monas20->id,
                'batch_no' => 'B-2026-04',
                'expiry_date' => '2026-09-30',
                'purchase_price' => 15.00,
                'selling_price' => 18.00,
                'current_stock' => 60,
                'is_active' => true,
            ],
            // Monas 40mg batches
            [
                'variant_id' => $monas40->id,
                'batch_no' => 'B-2026-05',
                'expiry_date' => '2027-05-20',
                'purchase_price' => 22.00,
                'selling_price' => 26.00,
                'current_stock' => 70,
                'is_active' => true,
            ],
        ];

        foreach ($batches as $batch) {
            \App\Models\InventoryBatch::create($batch);
        }
    }
}
