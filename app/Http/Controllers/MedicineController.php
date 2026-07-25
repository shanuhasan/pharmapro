<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Unit;
use App\Imports\MedicinesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class MedicineController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file'
        ]);

        try {
            Excel::import(new MedicinesImport, $request->file('import_file'));
            return redirect()->route('medicines.index')->with('success', 'Medicines imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('medicines.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Name', 'Generic Name', 'HSN Code', 'Category', 'Unit', 
                'Manufacturer', 'Description', 'Strips Per Box', 
                'Medicines Per Strip', 'Requires Prescription', 'Is Active'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, array_merge($headers, [
            'Content-Disposition' => 'attachment; filename="medicines_import_template.csv"',
        ]));
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Medicine::with(['medicineCategory', 'unit'])
                ->withSum('stock', 'quantity');

            if ($request->has('category_id') && !empty($request->category_id)) {
                $data->where('category_id', $request->category_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category', function($row){
                    return $row->medicineCategory ? $row->medicineCategory->name : 'N/A';
                })
                ->addColumn('unit', function($row){
                    return $row->unit ? $row->unit->abbreviation : 'N/A';
                })
                ->addColumn('stock_total', function($row){
                    return $row->stock_sum_quantity ?? 0;
                })
                ->addColumn('status', function($row){
                    return $row->is_active ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
                })
                ->addColumn('action', function($row){
                       $editUrl = route('medicines.edit', $row->id);
                       $deleteUrl = route('medicines.destroy', $row->id);
                       $btn = '<a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></a>';
                       $btn .= '<form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Delete?\');">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                    <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                </form>';
                        return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        
        $categories = MedicineCategory::all();
        return view('medicines.index', compact('categories'));
    }

    public function create()
    {
        $categories = MedicineCategory::all();
        $units = Unit::all();
        return view('medicines.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'hsn_code' => 'nullable|string|max:255',
            'category_id' => 'required|exists:medicine_categories,id',
            'unit_id' => 'required|exists:units,id',
            'manufacturer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'strips_per_box' => 'nullable|integer|min:1',
            'medicines_per_strip' => 'nullable|integer|min:1',
        ]);
        
        $validated['requires_prescription'] = $request->has('requires_prescription');
        $validated['is_active'] = $request->has('is_active');

        Medicine::create($validated);
        return redirect()->route('medicines.index')->with('success', 'Medicine created.');
    }

    public function edit(Medicine $medicine)
    {
        $categories = MedicineCategory::all();
        $units = Unit::all();
        return view('medicines.edit', compact('medicine', 'categories', 'units'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'hsn_code' => 'nullable|string|max:255',
            'category_id' => 'required|exists:medicine_categories,id',
            'unit_id' => 'required|exists:units,id',
            'manufacturer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'strips_per_box' => 'nullable|integer|min:1',
            'medicines_per_strip' => 'nullable|integer|min:1',
        ]);
        
        $validated['requires_prescription'] = $request->has('requires_prescription');
        $validated['is_active'] = $request->has('is_active');

        $medicine->update($validated);
        return redirect()->route('medicines.index')->with('success', 'Medicine updated.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', 'Medicine deleted.');
    }
}
