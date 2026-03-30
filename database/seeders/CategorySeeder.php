<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Medicine', 'slug' => 'medicine', 'type' => 'pharma', 'is_active' => true],
            ['name' => 'Vitamins & Supplements', 'slug' => 'vitamins-supplements', 'type' => 'pharma', 'is_active' => true],
            ['name' => 'Baby Care', 'slug' => 'baby-care', 'type' => 'pharma', 'is_active' => true],
            ['name' => 'Personal Care', 'slug' => 'personal-care', 'type' => 'cosmetics', 'is_active' => true],
            ['name' => 'Groceries', 'slug' => 'groceries', 'type' => 'grocery', 'is_active' => true],
            ['name' => 'General', 'slug' => 'general', 'type' => 'general', 'is_active' => true],
            ['name' => 'First Aid', 'slug' => 'first-aid', 'type' => 'pharma', 'is_active' => true],
            ['name' => 'Health Foods', 'slug' => 'health-foods', 'type' => 'pharma', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
