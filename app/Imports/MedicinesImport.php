<?php

namespace App\Imports;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MedicinesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Ensure name is provided, otherwise skip
        if (empty(trim($row['name']))) {
            return null;
        }

        // Handle Category
        $categoryName = trim($row['category'] ?? 'General');
        $category = MedicineCategory::firstOrCreate(
            ['name' => $categoryName],
            ['description' => 'Imported category']
        );

        // Handle Unit
        $unitName = trim($row['unit'] ?? 'Nos');
        $unit = Unit::firstOrCreate(
            ['name' => $unitName],
            ['abbreviation' => substr($unitName, 0, 3)]
        );

        $isActive = true;
        if (isset($row['is_active'])) {
            $val = strtolower(trim($row['is_active']));
            $isActive = in_array($val, ['yes', '1', 'true', 'active']);
        }

        $requiresPrescription = false;
        if (isset($row['requires_prescription'])) {
            $val = strtolower(trim($row['requires_prescription']));
            $requiresPrescription = in_array($val, ['yes', '1', 'true']);
        }

        return Medicine::updateOrCreate(
            [
                'name' => trim($row['name'])
            ],
            [
                'generic_name' => $row['generic_name'] ?? null,
                'hsn_code' => $row['hsn_code'] ?? null,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'manufacturer' => $row['manufacturer'] ?? null,
                'description' => $row['description'] ?? null,
                'strips_per_box' => $row['strips_per_box'] ?? null,
                'medicines_per_strip' => $row['medicines_per_strip'] ?? null,
                'requires_prescription' => $requiresPrescription,
                'is_active' => $isActive,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
        ];
    }
}
