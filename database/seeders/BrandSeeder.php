<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Beximco Pharma'],
            ['name' => 'Renata Limited'],
            ['name' => 'Square Pharmaceuticals'],
            ['name' => 'Incepta Pharmaceuticals'],
            ['name' => 'AstraZeneca'],
            ['name' => 'Pfizer'],
            ['name' => 'GSK'],
            ['name' => 'Novartis'],
            ['name' => 'Sanofi'],
            ['name' => 'DHL'],
            ['name' => 'ACI'],
            ['name' => 'Beacon'],
            ['name' => 'Opsonin'],
            ['name' => 'Popular'],
            ['name' => 'Eskayef'],
            ['name' => 'Orion'],
            ['name' => 'Ziska'],
            ['name' => 'PharmAid'],
            ['name' => 'Healthcare'],
            ['name' => 'Local Generic'],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::create($brand);
        }
    }
}
