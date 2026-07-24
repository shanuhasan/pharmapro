<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::create([
            'name' => 'Main Branch',
            'city' => 'Lahore',
            'is_active' => true,
        ]);

        Branch::create([
            'name' => 'DHA Branch',
            'city' => 'Lahore',
            'is_active' => true,
        ]);
    }
}
