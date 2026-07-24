<?php

namespace Database\Seeders;

use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Antibiotics', 'Painkillers', 'Vitamins', 'Syrups', 'Injections'];
        foreach ($categories as $category) {
            MedicineCategory::create(['name' => $category]);
        }
    }
}
