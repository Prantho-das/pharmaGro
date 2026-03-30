<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get units
        $stripUnit = \App\Models\Unit::where('short_name', 'str')->first();
        $tabUnit = \App\Models\Unit::where('short_name', 'tab')->first();

        if (!$stripUnit || !$tabUnit) {
            $this->command->error('Please seed units first!');
            return;
        }

        // Get products
        $napa = \App\Models\Product::where('name', 'Napa')->first();
        $monas = \App\Models\Product::where('name', 'Monas')->first();

        if (!$napa || !$monas) {
            $this->command->error('Please seed products first!');
            return;
        }

        $variants = [
            // Napa variants
            [
                'product_id' => $napa->id,
                'unit_id' => $tabUnit->id,
                'variant_name' => '500mg Strip',
                'sku' => 'NAPA-500-STR',
                'min_stock_alert' => 20,
            ],
            [
                'product_id' => $napa->id,
                'unit_id' => $stripUnit->id,
                'variant_name' => '650mg Strip',
                'sku' => 'NAPA-650-STR',
                'min_stock_alert' => 20,
            ],
            // Monas variants
            [
                'product_id' => $monas->id,
                'unit_id' => $tabUnit->id,
                'variant_name' => '20mg Strip',
                'sku' => 'MONAS-20-STR',
                'min_stock_alert' => 15,
            ],
            [
                'product_id' => $monas->id,
                'unit_id' => $stripUnit->id,
                'variant_name' => '40mg Strip',
                'sku' => 'MONAS-40-STR',
                'min_stock_alert' => 15,
            ],
        ];

        foreach ($variants as $variant) {
            \App\Models\ProductVariant::create($variant);
        }
    }
}
