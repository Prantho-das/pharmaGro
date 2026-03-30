<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            SettingSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            InventoryBatchSeeder::class,
            CustomerSeeder::class,
            StaffSeeder::class,
            SaleSeeder::class,
            SaleItemSeeder::class,
        ]);
    }
}
