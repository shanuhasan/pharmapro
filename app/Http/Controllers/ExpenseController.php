<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->role === 'admin' ? $request->get('branch_id') : auth()->user()->branch_id;

        if ($request->ajax()) {
            $data = Expense::with(['branch', 'user']);
            if ($branchId) {
                $data->where('branch_id', $branchId);
            }
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('expense_date', function($row){
                    return Carbon::parse($row->expense_date)->format('d M Y');
                })
                ->editColumn('amount', function($row){
                    return '$' . number_format($row->amount, 2);
                })
                ->addColumn('action', function($row){
                    $deleteUrl = route('expenses.destroy', $row->id);
                    return '<form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Delete this expense?\');">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Summary Calculations
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalExpenses = Expense::whereMonth('expense_date', $currentMonth)
            ->whereYear('expense_date', $currentYear);
        if ($branchId) $totalExpenses->where('branch_id', $branchId);
        $totalExpenses = $totalExpenses->sum('amount');

        $totalRevenue = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('status', 'completed');
        if ($branchId) $totalRevenue->where('branch_id', $branchId);
        $totalRevenue = $totalRevenue->sum('total_amount');

        $profit = $totalRevenue - $totalExpenses;

        $branches = \App\Models\Branch::all();

        return view('expenses.index', compact('totalExpenses', 'totalRevenue', 'profit', 'branches', 'branchId'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::active()->get();
        return view('expenses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
