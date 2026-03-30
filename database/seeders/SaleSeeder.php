<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get staff (first user)
        $staff = \App\Models\User::first();

        if (!$staff) {
            $this->command->error('Please create a user first!');
            return;
        }

        // Get customer (walking)
        $customer = \App\Models\Customer::where('phone', null)->first();

        if (!$customer) {
            $this->command->error('Please seed customers first!');
            return;
        }

        $sales = [
            [
                'invoice_no' => 'INV-2026-03-30-0001',
                'customer_id' => $customer->id,
                'sub_total' => 30.00,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'grand_total' => 30.00,
                'paid_amount' => 30.00,
                'due_amount' => 0,
                'payment_method' => 'cash',
                'sold_by_id' => $staff->id,
                'created_at' => now()->subDays(2),
            ],
            [
                'invoice_no' => 'INV-2026-03-29-0002',
                'customer_id' => $customer->id,
                'sub_total' => 54.00,
                'tax_amount' => 0,
                'discount_amount' => 5.00,
                'grand_total' => 49.00,
                'paid_amount' => 30.00,
                'due_amount' => 19.00,
                'payment_method' => 'bkash',
                'sold_by_id' => $staff->id,
                'created_at' => now()->subDays(1),
            ],
        ];

        foreach ($sales as $saleData) {
            \App\Models\Sale::create($saleData);
        }
    }
}
