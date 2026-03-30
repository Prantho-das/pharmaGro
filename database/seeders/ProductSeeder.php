<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and brands
        $medicineCategory = \App\Models\Category::where('slug', 'medicine')->first();
        $brand = \App\Models\Brand::where('name', 'Beximco Pharma')->first();

        if (!$medicineCategory || !$brand) {
            $this->command->error('Please seed categories and brands first!');
            return;
        }

        $products = [
            [
                'name' => 'Napa',
                'generic_name' => 'Paracetamol',
                'is_medicine' => true,
                'has_variants' => true,
                'category_id' => $medicineCategory->id,
                'brand_id' => $brand->id,
            ],
            [
                'name' => 'Monas',
                'generic_name' => 'Omeprazole',
                'is_medicine' => true,
                'has_variants' => true,
                'category_id' => $medicineCategory->id,
                'brand_id' => $brand->id,
            ],
            [
                'name' => 'Nitrolin',
                'generic_name' => 'Glyceryl Trinitrate',
                'is_medicine' => true,
                'has_variants' => true,
                'category_id' => $medicineCategory->id,
                'brand_id' => $brand->id,
            ],
        ];

        foreach ($products as $productData) {
            \App\Models\Product::create($productData);
        }
    }
}
