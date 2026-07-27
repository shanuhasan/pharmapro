<?php

namespace App\Http\Controllers;

use App\Models\MedicineCategory;
use Illuminate\Http\Request;

class MedicineCategoryController extends Controller
{
    public function index()
    {
        $categories = MedicineCategory::latest()->paginate(10);
        return view('medicine_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('medicine_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name',
        ]);
        MedicineCategory::create($validated);
        return redirect()->route('medicine-categories.index')->with('success', 'Category created.');
    }

    public function edit(MedicineCategory $medicineCategory)
    {
        return view('medicine_categories.edit', compact('medicineCategory'));
    }

    public function update(Request $request, MedicineCategory $medicineCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name,'.$medicineCategory->id,
        ]);
        $medicineCategory->update($validated);
        return redirect()->route('medicine-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(MedicineCategory $medicineCategory)
    {
        $medicineCategory->delete();
        return redirect()->route('medicine-categories.index')->with('success', 'Category deleted.');
    }
}
