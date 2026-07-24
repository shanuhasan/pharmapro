<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                       $showUrl = route('customers.show', $row->id);
                       $editUrl = route('customers.edit', $row->id);
                       $deleteUrl = route('customers.destroy', $row->id);
                       
                       $btn = '<a href="'.$showUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-2" title="View Profile"><i class="fas fa-eye"></i></a>';
                       $btn .= '<a href="'.$editUrl.'" class="text-blue-600 hover:text-blue-900 mr-2" title="Edit"><i class="fas fa-edit"></i></a>';
                       $btn .= '<form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Delete this customer?\');">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>';
                        return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('customers.index');
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => function($query) {
            $query->orderBy('sale_date', 'desc');
        }]);

        $totalSpent = $customer->sales->sum('total_amount');
        $lastVisit = $customer->sales->first() ? $customer->sales->first()->sale_date : 'Never';

        return view('customers.show', compact('customer', 'totalSpent', 'lastVisit'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
