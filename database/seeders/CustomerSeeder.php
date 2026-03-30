<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Walking Customer',
                'phone' => null,
                'points' => 0,
            ],
            [
                'name' => 'Rahim Ahmed',
                'phone' => '01712345678',
                'points' => 150,
            ],
            [
                'name' => 'Fatima Begum',
                'phone' => '01898765432',
                'points' => 320,
            ],
            [
                'name' => 'Karim Uddin',
                'phone' => '01655551234',
                'points' => 75,
            ],
        ];

        foreach ($customers as $customer) {
            \App\Models\Customer::create($customer);
        }
    }
}
