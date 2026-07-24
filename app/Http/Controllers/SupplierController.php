<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function($row){
                    return $row->is_active ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
                })
                ->addColumn('action', function($row){
                       $showUrl = route('suppliers.show', $row->id);
                       $editUrl = route('suppliers.edit', $row->id);
                       $deleteUrl = route('suppliers.destroy', $row->id);
                       
                       $btn = '<a href="'.$showUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-2" title="View"><i class="fas fa-eye"></i></a>';
                       $btn .= '<a href="'.$editUrl.'" class="text-blue-600 hover:text-blue-900 mr-2" title="Edit"><i class="fas fa-edit"></i></a>';
                       $btn .= '<form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Delete?\');">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>';
                        return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        
        return view('suppliers.index');
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'ntn_number' => 'nullable|string|max:50',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => function($query) {
            $query->orderBy('purchase_date', 'desc');
        }]);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'ntn_number' => 'nullable|string|max:50',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
