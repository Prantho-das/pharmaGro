<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'pc'],
            ['name' => 'Strip', 'short_name' => 'str'],
            ['name' => 'Box', 'short_name' => 'bx'],
            ['name' => 'Packet', 'short_name' => 'pkt'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Milliliter', 'short_name' => 'ml'],
            ['name' => 'Liter', 'short_name' => 'ltr'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Centimeter', 'short_name' => 'cm'],
            ['name' => 'Tablet', 'short_name' => 'tab'],
            ['name' => 'Capsule', 'short_name' => 'cap'],
            ['name' => 'Ampoule', 'short_name' => 'amp'],
            ['name' => 'Vial', 'short_name' => 'via'],
            ['name' => 'Can', 'short_name' => 'can'],
            ['name' => 'Bottle', 'short_name' => 'btl'],
            ['name' => 'Tube', 'short_name' => 'tub'],
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::create($unit);
        }
    }
}
