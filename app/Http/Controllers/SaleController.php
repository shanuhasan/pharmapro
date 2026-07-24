<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Sale::with(['branch', 'customer'])->select('sales.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('branch', function($row){
                    return $row->branch ? $row->branch->name : 'N/A';
                })
                ->addColumn('customer', function($row){
                    if ($row->customer) {
                        return $row->customer->name;
                    } elseif ($row->customer_name) {
                        return $row->customer_name . ' (Walk-in)';
                    }
                    return 'Walk-in Customer';
                })
                ->editColumn('total_amount', function($row){
                    return setting('currency_symbol', '₹') . number_format($row->total_amount, 2);
                })
                ->editColumn('sale_date', function($row){
                    return \Carbon\Carbon::parse($row->sale_date)->format('Y-m-d');
                })
                ->addColumn('action', function($row){
                       $showUrl = route('sales.show', $row->id);
                       $printUrl = route('invoice.print', $row->id);
                       $btn = '<a href="'.$showUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-eye"></i> View</a>';
                       $btn .= '<a href="'.$printUrl.'" target="_blank" class="text-green-600 hover:text-green-900"><i class="fas fa-print"></i> Print</a>';
                       return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('sales.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.stock_id' => 'required|exists:stock,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sale_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'sale_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // We will store manual customer directly in the sales table
            $customerName = null;
            $customerPhone = null;
            $customerAddress = null;
            
            if (empty($request->customer_id) && !empty($request->new_customer_name)) {
                $customerName = $request->new_customer_name;
                $customerPhone = $request->new_customer_phone ?? null;
                $customerAddress = $request->new_customer_address ?? null;
            }

            $branchId = (auth()->user()->role === 'admin' && $request->has('branch_id')) 
                ? $request->branch_id 
                : auth()->user()->branch_id;
            
            // Generate Invoice Number
            $saleDate = $request->sale_date ? \Carbon\Carbon::parse($request->sale_date) : now();
            $prefix = setting('invoice_prefix', 'INV-');
            $dateStr = $saleDate->format('Ymd');
            $lastSale = Sale::whereDate('sale_date', $saleDate->format('Y-m-d'))->where('branch_id', $branchId)->count();
            $invoiceNumber = $prefix . $branchId . '-' . $dateStr . '-' . str_pad($lastSale + 1, 3, '0', STR_PAD_LEFT);

            // Create Sale Record
            $sale = Sale::create([
                'branch_id' => $branchId,
                'customer_id' => $request->customer_id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_address' => $customerAddress,
                'doctor_name' => $request->doctor_name ?? null,
                'doctor_address' => $request->doctor_address ?? null,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $saleDate->format('Y-m-d'),
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'tax' => $request->tax,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->change_amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            // Create Items & Deduct Stock
            foreach ($request->items as $item) {
                // Fetch stock
                $stock = Stock::findOrFail($item['stock_id']);
                
                // Safety check
                if ($stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for batch " . $stock->batch_number);
                }

                $oldQty = $stock->quantity;
                $stock->quantity -= $item['quantity'];
                $stock->save();

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $item['medicine_id'],
                    'stock_id' => $stock->id,
                    'batch_number' => $stock->batch_number,
                    'quantity' => $item['quantity'],
                    'sale_price' => $item['sale_price'],
                    'discount' => 0, // Item level discount
                    'total' => $item['total'],
                ]);

                // Log Stock
                StockLog::create([
                    'stock_id' => $stock->id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_out', // Effectively an outgoing movement
                    'quantity_changed' => -$item['quantity'],
                    'previous_quantity' => $oldQty,
                    'new_quantity' => $stock->quantity,
                    'reason' => "Sale Checkout Invoice " . $invoiceNumber,
                    'reference_id' => $sale->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Sale completed successfully.', 
                'invoice_number' => $invoiceNumber,
                'sale_id' => $sale->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function show(Sale $sale)
    {
        $sale->load(['saleItems.medicine', 'customer', 'branch']);
        return view('sales.show', compact('sale'));
    }
}
