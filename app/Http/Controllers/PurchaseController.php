<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Medicine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Purchase::with(['branch', 'supplier'])->select('purchases.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('branch', function($row){
                    return $row->branch ? $row->branch->name : 'N/A';
                })
                ->addColumn('supplier', function($row){
                    return $row->supplier ? $row->supplier->name : 'N/A';
                })
                ->editColumn('total_amount', function($row){
                    return setting('currency_symbol', '₹') . number_format($row->total_amount, 2);
                })
                ->addColumn('action', function($row){
                       $showUrl = route('purchases.show', $row->id);
                       $editUrl = route('purchases.edit', $row->id);
                       $btn = '<a href="'.$showUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-eye"></i> View</a>';
                       $btn .= '<a href="'.$editUrl.'" class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i> Edit</a>';
                       return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('purchases.index');
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::all();
        $medicines = Medicine::where('is_active', true)->get();
        return view('purchases.create', compact('branches', 'suppliers', 'medicines'));
    }

    public function store(Request $request, StockService $stockService)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|unique:purchases,invoice_number',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'nullable|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.sale_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $purchase = Purchase::create([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['purchase_price'];
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'batch_number' => $item['batch_number'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'sale_price' => $item['sale_price'],
                    'total' => $itemTotal,
                ]);

                // Update Stock
                $stockService->addStock($request->branch_id, $item);
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording purchase: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show(Purchase $purchase)
    {
        $purchase->load(['branch', 'supplier', 'purchaseItems.medicine']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['purchaseItems.medicine']);
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::all();
        $medicines = Medicine::where('is_active', true)->get();
        return view('purchases.edit', compact('purchase', 'branches', 'suppliers', 'medicines'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|unique:purchases,invoice_number,' . $purchase->id,
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'nullable|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.sale_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate net changes
            $netChanges = [];
            $oldBranchId = $purchase->branch_id;
            
            foreach ($purchase->purchaseItems as $oldItem) {
                $key = "{$oldBranchId}-{$oldItem->medicine_id}-{$oldItem->batch_number}";
                if (!isset($netChanges[$key])) {
                    $netChanges[$key] = [
                        'branch_id' => $oldBranchId,
                        'medicine_id' => $oldItem->medicine_id,
                        'batch_number' => $oldItem->batch_number,
                        'qty_change' => 0,
                    ];
                }
                $netChanges[$key]['qty_change'] -= $oldItem->quantity;
            }

            $newBranchId = $request->branch_id;
            foreach ($request->items as $newItem) {
                $key = "{$newBranchId}-{$newItem['medicine_id']}-{$newItem['batch_number']}";
                if (!isset($netChanges[$key])) {
                    $netChanges[$key] = [
                        'branch_id' => $newBranchId,
                        'medicine_id' => $newItem['medicine_id'],
                        'batch_number' => $newItem['batch_number'],
                        'qty_change' => 0,
                    ];
                }
                $netChanges[$key]['qty_change'] += $newItem['quantity'];
                $netChanges[$key]['expiry_date'] = $newItem['expiry_date'] ?? null;
                $netChanges[$key]['purchase_price'] = $newItem['purchase_price'];
                $netChanges[$key]['sale_price'] = $newItem['sale_price'];
            }

            // Validate stock
            foreach ($netChanges as $change) {
                if ($change['qty_change'] < 0) {
                    $stock = \App\Models\Stock::where('branch_id', $change['branch_id'])
                        ->where('medicine_id', $change['medicine_id'])
                        ->where('batch_number', $change['batch_number'])
                        ->first();
                        
                    if (!$stock || $stock->quantity < abs($change['qty_change'])) {
                        throw new \Exception("Cannot reduce stock for batch {$change['batch_number']}. Items have already been used or sold.");
                    }
                }
            }

            // Apply changes
            foreach ($netChanges as $change) {
                if ($change['qty_change'] !== 0 || isset($change['purchase_price'])) {
                    $stock = \App\Models\Stock::where('branch_id', $change['branch_id'])
                        ->where('medicine_id', $change['medicine_id'])
                        ->where('batch_number', $change['batch_number'])
                        ->first();
                    
                    if ($stock) {
                        $stock->quantity += $change['qty_change'];
                        if (isset($change['purchase_price'])) {
                            $stock->purchase_price = $change['purchase_price'];
                            $stock->sale_price = $change['sale_price'];
                            $stock->expiry_date = $change['expiry_date'] ?? $stock->expiry_date;
                        }
                        $stock->save();
                    } else if ($change['qty_change'] > 0) {
                        \App\Models\Stock::create([
                            'branch_id' => $change['branch_id'],
                            'medicine_id' => $change['medicine_id'],
                            'batch_number' => $change['batch_number'],
                            'expiry_date' => $change['expiry_date'] ?? null,
                            'quantity' => $change['qty_change'],
                            'purchase_price' => $change['purchase_price'],
                            'sale_price' => $change['sale_price'],
                        ]);
                    }
                }
            }

            // Update purchase
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $purchase->update([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
            ]);

            // Replace items
            $purchase->purchaseItems()->delete();
            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'batch_number' => $item['batch_number'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'sale_price' => $item['sale_price'],
                    'total' => $item['quantity'] * $item['purchase_price'],
                ]);
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated and stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage())->withInput();
        }
    }
}
