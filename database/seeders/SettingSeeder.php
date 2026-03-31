<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'is_pharmacy_active'],
            ['value' => 'true']
        );

        // Default tax rate: 0% (can be changed in admin settings)
        Setting::updateOrCreate(
            ['key' => 'tax_rate'],
            ['value' => '0']
        );

        // Loyalty points: points per BDT 100 spent (default 1 point per 100)
        Setting::updateOrCreate(
            ['key' => 'points_rate'],
            ['value' => '0.01'] // 0.01 means 1 point per 100 BDT
        );
    }
}
