<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Tablet', 'abbreviation' => 'Tab'],
            ['name' => 'Syrup', 'abbreviation' => 'Syp'],
            ['name' => 'Injection', 'abbreviation' => 'Inj'],
            ['name' => 'Capsule', 'abbreviation' => 'Cap'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
