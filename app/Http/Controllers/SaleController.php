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
            $data = Sale::with(['branch', 'customer'])->select('sales.*')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('branch', function($row){
                    return $row->branch ? $row->branch->name : 'N/A';
                })
                ->addColumn('customer', function($row){
                    $name = $row->customer_name ?: ($row->customer ? $row->customer->name : null);
                    if ($name) {
                        return $row->customer ? $name : $name . ' (Walk-in)';
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
                       $currentName = $row->customer_name ?: ($row->customer ? $row->customer->name : '');
                       $currentPhone = $row->customer_phone ?: ($row->customer ? $row->customer->phone : '');
                       $currentAddress = $row->customer_address ?: ($row->customer ? $row->customer->address : '');
                       $currentDoctor = $row->doctor_name ?? '';
                       $currentDoctorAddress = $row->doctor_address ?? '';

                       $btn = '<a href="'.$showUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3" title="View"><i class="fas fa-eye"></i> View</a>';
                       $btn .= '<button type="button" class="text-blue-600 hover:text-blue-900 mr-3 edit-patient-btn" '
                           . 'data-id="'.$row->id.'" '
                           . 'data-invoice="'.e($row->invoice_number).'" '
                           . 'data-name="'.e($currentName).'" '
                           . 'data-phone="'.e($currentPhone).'" '
                           . 'data-address="'.e($currentAddress).'" '
                           . 'data-doctor="'.e($currentDoctor).'" '
                           . 'data-doctor-address="'.e($currentDoctorAddress).'" '
                           . 'data-customer-id="'.($row->customer_id ?? '').'" '
                           . 'title="Edit Patient"><i class="fas fa-user-edit"></i> Edit Patient</button>';
                       $btn .= '<a href="'.$printUrl.'" target="_blank" class="text-green-600 hover:text-green-900" title="Print"><i class="fas fa-print"></i> Print</a>';
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

    public function edit(Sale $sale)
    {
        $sale->load(['customer', 'branch', 'saleItems.medicine']);
        return view('sales.edit', compact('sale'));
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'patient_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'patient_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:1000',
            'patient_address' => 'nullable|string|max:1000',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_address' => 'nullable|string|max:1000',
        ]);

        $patientName = $request->input('patient_name', $request->input('customer_name'));
        $patientPhone = $request->input('patient_phone', $request->input('customer_phone'));
        $patientAddress = $request->input('patient_address', $request->input('customer_address'));
        $customerId = $request->filled('customer_id') ? $request->customer_id : null;

        // If a customer_id is supplied and name is blank, default to that customer's details
        if ($customerId && empty($patientName)) {
            $customer = \App\Models\Customer::find($customerId);
            if ($customer) {
                $patientName = $customer->name;
                if (empty($patientPhone)) $patientPhone = $customer->phone;
                if (empty($patientAddress)) $patientAddress = $customer->address;
            }
        }

        $sale->update([
            'customer_id' => $customerId,
            'customer_name' => $patientName,
            'customer_phone' => $patientPhone,
            'customer_address' => $patientAddress,
            'doctor_name' => $request->doctor_name,
            'doctor_address' => $request->doctor_address,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice patient details updated successfully.',
                'sale' => $sale->fresh(['customer'])
            ]);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Invoice patient details updated successfully.');
    }
}
