<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\Customer;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $branches = \App\Models\Branch::all();
        return view('pos.index', compact('branches'));
    }

    public function searchMedicine(Request $request)
    {
        $query = $request->get('q');
        $medicines = Medicine::where('name', 'like', "%{$query}%")
            ->orWhere('generic_name', 'like', "%{$query}%")
            ->where('is_active', true)
            ->with('medicineCategory')
            ->limit(10)
            ->get();
            
        return response()->json($medicines);
    }

    public function checkStock(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        $branchId = auth()->user()->role === 'admin' && $request->has('branch_id') 
            ? $request->get('branch_id') 
            : auth()->user()->branch_id;

        $stock = Stock::with('medicine')
            ->where('medicine_id', $medicineId)
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc') // FIFO sorting
            ->get();

        return response()->json($stock);
    }

    public function searchCustomer(Request $request)
    {
        $query = $request->get('q');
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get();
            
        return response()->json($customers);
    }
}
