<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Stock::with(['branch', 'medicine'])->select('stock.*')->latest('stock.created_at');
            
            if ($request->has('branch_id') && !empty($request->branch_id)) {
                $data->where('branch_id', $request->branch_id);
            } elseif (auth()->user()->role !== 'admin') {
                $data->where('branch_id', auth()->user()->branch_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('branch', function ($row) {
                    return $row->branch->name ?? 'N/A';
                })
                ->addColumn('medicine', function ($row) {
                    return $row->medicine->name ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $badges = '';
                    if ($row->quantity < setting('low_stock_threshold', 10)) {
                        $badges .= '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 mr-1">Low Stock</span>';
                    }
                    if (\Carbon\Carbon::parse($row->expiry_date)->isPast()) {
                        $badges .= '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-800 text-white mr-1">Expired</span>';
                    } elseif (\Carbon\Carbon::parse($row->expiry_date)->diffInDays(now()) <= 90) {
                        $badges .= '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 mr-1">Expiring Soon</span>';
                    }
                    return $badges ?: '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">OK</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button data-id="'.$row->id.'" data-qty="'.$row->quantity.'" class="adjust-stock text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-edit"></i> Adjust</button>
                        <button data-id="'.$row->id.'" data-qty="'.$row->quantity.'" class="transfer-stock text-indigo-600 hover:text-indigo-900"><i class="fas fa-exchange-alt"></i> Transfer</button>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        
        $branches = Branch::all();
        return view('stock.index', compact('branches'));
    }

    public function adjust(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $oldQty = $stock->quantity;
            $stock->quantity = $validated['new_quantity'];
            $stock->save();

            StockLog::create([
                'stock_id' => $stock->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity_changed' => $validated['new_quantity'] - $oldQty,
                'previous_quantity' => $oldQty,
                'new_quantity' => $validated['new_quantity'],
                'reason' => $validated['reason'],
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock adjusted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function transfer(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'destination_branch_id' => 'required|exists:branches,id',
            'transfer_quantity' => 'required|integer|min:1|max:'.$stock->quantity,
            'reason' => 'nullable|string|max:255',
        ]);

        if ($stock->branch_id == $validated['destination_branch_id']) {
            return response()->json(['success' => false, 'message' => 'Cannot transfer to the same branch.'], 422);
        }

        DB::beginTransaction();
        try {
            // Deduct from Source
            $oldQty = $stock->quantity;
            $stock->quantity -= $validated['transfer_quantity'];
            $stock->save();

            $sourceLog = StockLog::create([
                'stock_id' => $stock->id,
                'user_id' => auth()->id(),
                'type' => 'transfer_out',
                'quantity_changed' => -$validated['transfer_quantity'],
                'previous_quantity' => $oldQty,
                'new_quantity' => $stock->quantity,
                'reason' => $validated['reason'] ?? 'Transfer to branch ' . $validated['destination_branch_id'],
            ]);

            // Add to Destination
            $destStock = Stock::where('branch_id', $validated['destination_branch_id'])
                ->where('medicine_id', $stock->medicine_id)
                ->where('batch_number', $stock->batch_number)
                ->first();

            if ($destStock) {
                $destOldQty = $destStock->quantity;
                $destStock->quantity += $validated['transfer_quantity'];
                $destStock->save();
            } else {
                $destOldQty = 0;
                $destStock = Stock::create([
                    'branch_id' => $validated['destination_branch_id'],
                    'medicine_id' => $stock->medicine_id,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date,
                    'quantity' => $validated['transfer_quantity'],
                    'purchase_price' => $stock->purchase_price,
                    'sale_price' => $stock->sale_price,
                ]);
            }

            StockLog::create([
                'stock_id' => $destStock->id,
                'user_id' => auth()->id(),
                'type' => 'transfer_in',
                'quantity_changed' => $validated['transfer_quantity'],
                'previous_quantity' => $destOldQty,
                'new_quantity' => $destStock->quantity,
                'reason' => $validated['reason'] ?? 'Transfer from branch ' . $stock->branch_id,
                'reference_id' => $sourceLog->id,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
