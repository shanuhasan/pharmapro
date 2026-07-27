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
            $data = Purchase::with(['branch', 'supplier'])->select('purchases.*')->latest();
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
        $pharmacyId = auth()->user()->pharmacy_id;
        if (!$pharmacyId && $request->branch_id) {
            $branch = \App\Models\Branch::find($request->branch_id);
            if ($branch) $pharmacyId = $branch->pharmacy_id;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('purchases', 'invoice_number')->where(function ($query) use ($pharmacyId) {
                    return $query->where('pharmacy_id', $pharmacyId);
                })
            ],
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

        if ($validator->fails()) {
            \Log::error('Purchase validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += isset($item['item_total']) && $item['item_total'] > 0 
                                ? (float)$item['item_total'] 
                                : ($item['quantity'] * $item['purchase_price']);
            }

            $purchase = Purchase::create([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'extra_charges' => $request->extra_charges ?? 0,
                'total_amount' => $totalAmount + ($request->extra_charges ?? 0),
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $itemTotal = isset($item['item_total']) && $item['item_total'] > 0 
                             ? (float)$item['item_total'] 
                             : ($item['quantity'] * $item['purchase_price']);
                
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
            \Log::error('Error recording purchase: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $pharmacyId = auth()->user()->pharmacy_id;
        if (!$pharmacyId && $request->branch_id) {
            $branch = \App\Models\Branch::find($request->branch_id);
            if ($branch) $pharmacyId = $branch->pharmacy_id;
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('purchases', 'invoice_number')->ignore($purchase->id)->where(function ($query) use ($pharmacyId) {
                    return $query->where('pharmacy_id', $pharmacyId);
                })
            ],
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
                $totalAmount += isset($item['item_total']) && $item['item_total'] > 0 
                                ? (float)$item['item_total'] 
                                : ($item['quantity'] * $item['purchase_price']);
            }

            $purchase->update([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'extra_charges' => $request->extra_charges ?? 0,
                'total_amount' => $totalAmount + ($request->extra_charges ?? 0),
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
                    'total' => isset($item['item_total']) && $item['item_total'] > 0 
                               ? (float)$item['item_total'] 
                               : ($item['quantity'] * $item['purchase_price']),
                ]);
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated and stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating purchase: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (count($rows) === 0) {
                return response()->json(['error' => 'File is empty'], 400);
            }

            $items = [];
            $supplierName = null;
            $invoiceNumber = null;
            $purchaseDate = null;
            $overrideInvoiceNumber = null;
            
            $isAwacsFormat = false;
            if (isset($rows[0][0]) && trim($rows[0][0]) === 'TypeofRecord') {
                $isAwacsFormat = true;

                $fileName = $file->getClientOriginalName();
                if (preg_match('/_([A-Z0-9]+)_with_header\.csv$/i', $fileName, $matches)) {
                    $overrideInvoiceNumber = $matches[1];
                }
            }

            $extraCharges = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header row
                
                $parsedData = null;
                if ($isAwacsFormat) {
                    $parsedData = $this->parseAwacsRow($row, $supplierName, $invoiceNumber, $purchaseDate);
                } else {
                    $parsedData = $this->parseStandardRow($row, $supplierName, $invoiceNumber, $purchaseDate);
                }

                if (!$parsedData) continue;
                
                if (!empty($parsedData['isExtraCharge'])) {
                    $amount = (float)($parsedData['taxableAmtStr'] ?? 0);
                    if ($amount == 0) {
                        $qty = (float)$parsedData['qtyStr'];
                        $srate = (float)$parsedData['srateStr'];
                        $amount = ($qty * $srate);
                        if ($amount == 0 && (float)$parsedData['mrpStr'] > 0) {
                            $amount = (float)$parsedData['mrpStr'];
                        }
                    }
                    $extraCharges += $amount + ($amount * 0.18);
                    continue;
                }

                $items[] = $this->processParsedRow($parsedData, $supplierName);
            }

            $supplierId = null;
            if ($supplierName) {
                $supplier = Supplier::where('name', 'LIKE', '%' . trim($supplierName) . '%')->first();
                if ($supplier) {
                    $supplierId = $supplier->id;
                }
            }

            if ($overrideInvoiceNumber) {
                $invoiceNumber = $overrideInvoiceNumber;
            }

            return response()->json([
                'success' => true,
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
                'invoice_number' => $invoiceNumber,
                'purchase_date' => $purchaseDate,
                'extra_charges' => $extraCharges,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel import failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 500);
        }
    }

    private function parseAwacsRow($row, &$supplierName, &$invoiceNumber, &$purchaseDate)
    {
        if (empty($row[5])) return null;
        
        if (!$invoiceNumber) {
            $supplierName = $row[2] ?? null;
            $invoiceNumber = 'AWACS-' . time();
            $purchaseDate = date('Y-m-d');
        }
        
        $medicineName = $row[5];
        $isExtraCharge = false;
        if (preg_match('/(Platform Fees|COD Charges)/i', $medicineName)) {
            $isExtraCharge = true;
        }
        $packStr = $row[6] ?? '';
        $batchStr = $row[8] ?? null;
        $expiryStr = $row[9] ?? null;
        $qtyStr = $row[15] ?? 0;
        $fqtyStr = $row[16] ?? 0;
        $srateStr = $row[11] ?? 0;
        $mrpStr = $row[12] ?? 0;
        $disStr = $row[17] ?? 0;
        $taxableAmtStr = $row[21] ?? 0;
        $halfpStr = 0;
        $cgstStr = $row[22] ?? 0;
        $igstStr = $row[24] ?? 0;
        $sgstStr = $row[26] ?? 0;
        $hsnStr = $row[30] ?? null;
        
        $medicinesPerStrip = 1;
        if (preg_match('/\(?(\d+)\s*(Tab|Cap|Capsule|Tablet)s?\)?/i', $medicineName, $m)) {
            $medicinesPerStrip = (int)$m[1];
            if ($medicinesPerStrip <= 0) $medicinesPerStrip = 1;
        }
        
        $formattedExpiry = null;
        if ($expiryStr && strlen(trim($expiryStr)) >= 6) {
            try {
                $formattedExpiry = \Carbon\Carbon::createFromFormat('dmY', trim($expiryStr))->endOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $formattedExpiry = null;
            }
        }

        return compact(
            'medicineName', 'packStr', 'batchStr', 'formattedExpiry', 'qtyStr', 'fqtyStr', 
            'srateStr', 'mrpStr', 'disStr', 'halfpStr', 'cgstStr', 'sgstStr', 'igstStr', 'hsnStr', 'medicinesPerStrip', 'isExtraCharge', 'taxableAmtStr'
        );
    }

    private function parseStandardRow($row, &$supplierName, &$invoiceNumber, &$purchaseDate)
    {
        if (empty($row[6]) || empty($row[1])) return null;
        
        if (!$invoiceNumber) {
            $supplierName = $row[0] ?? null;
            $invoiceNumber = $row[1] ?? null;
            
            $rawDate = $row[2] ?? null;
            if ($rawDate) {
                try {
                    $purchaseDate = \Carbon\Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $purchaseDate = \Carbon\Carbon::parse(str_replace('/', '-', $rawDate))->format('Y-m-d');
                    } catch (\Exception $e) {
                        $purchaseDate = null;
                    }
                }
            }
        }

        $medicineName = $row[6];
        $isExtraCharge = false;
        if (preg_match('/(Platform Fees|COD Charges)/i', $medicineName)) {
            $isExtraCharge = true;
        }
        $packStr = $row[7] ?? '';
        $batchStr = $row[8] ?? null;
        $expiryStr = $row[9] ?? null;
        $qtyStr = $row[10] ?? 0;
        $fqtyStr = $row[11] ?? 0;
        $srateStr = $row[14] ?? 0;
        $mrpStr = $row[15] ?? 0;
        $disStr = $row[16] ?? 0;
        $taxableAmtStr = $row[20] ?? 0;
        $halfpStr = $row[12] ?? 0;
        $cgstStr = $row[26] ?? 0;
        $sgstStr = $row[27] ?? 0;
        $igstStr = 0;
        $hsnStr = $row[25] ?? null;

        $medicinesPerStrip = 1;
        if (preg_match('/^(\d+)\s*[`\'’]?\s*S$/i', trim($packStr), $matches)) {
            $medicinesPerStrip = (int)$matches[1];
            if ($medicinesPerStrip <= 0) $medicinesPerStrip = 1;
        }
        
        $formattedExpiry = null;
        if ($expiryStr) {
            try {
                $formattedExpiry = \Carbon\Carbon::createFromFormat('m/y', $expiryStr)->endOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $formattedExpiry = null;
            }
        }

        return compact(
            'medicineName', 'packStr', 'batchStr', 'formattedExpiry', 'qtyStr', 'fqtyStr', 
            'srateStr', 'mrpStr', 'disStr', 'halfpStr', 'cgstStr', 'sgstStr', 'igstStr', 'hsnStr', 'medicinesPerStrip', 'isExtraCharge', 'taxableAmtStr'
        );
    }

    private function processParsedRow($parsedData, $supplierName)
    {
        extract($parsedData);

        $cleanName = preg_replace('/[*#]$/', '', trim($medicineName));
        $medicine = Medicine::where('name', 'LIKE', '%' . $cleanName . '%')->first();

        if (!$medicine) {
            $defaultCategory = \App\Models\MedicineCategory::firstOrCreate(['name' => 'General']);
            $defaultUnit = \App\Models\Unit::firstOrCreate(['name' => 'Strip', 'abbreviation' => 'Str']);
            
            $pharmacyId = auth()->user()->pharmacy_id;
            if (!$pharmacyId) {
                $branch = \App\Models\Branch::first();
                if ($branch) $pharmacyId = $branch->pharmacy_id;
            }

            $medicine = Medicine::create([
                'name' => $cleanName,
                'medicines_per_strip' => $medicinesPerStrip,
                'hsn_code' => $hsnStr,
                'category_id' => $defaultCategory->id,
                'unit_id' => $defaultUnit->id,
                'manufacturer' => $supplierName ?? null,
                'is_active' => true,
                'pharmacy_id' => $pharmacyId
            ]);
        } else {
            if ($medicinesPerStrip > 1 && $medicine->medicines_per_strip != $medicinesPerStrip) {
                $medicine->update(['medicines_per_strip' => $medicinesPerStrip]);
            }
        }

        $qty = (float)$qtyStr;
        $fqty = (float)$fqtyStr;
        $srate = (float)$srateStr;
        
        $disStrClean = str_replace('%', '', trim($disStr ?? '0'));
        $dis = (float)$disStrClean;
        
        $halfpStrClean = strtolower(str_replace('%', '', trim($halfpStr ?? '0')));
        if (in_array($halfpStrClean, ['y', 'yes', 'h', 'true'])) {
            $halfp = 0.5;
        } else {
            $halfp = (float)$halfpStrClean;
        }
        
        $totalQty = $qty + $fqty;
        
        $grossTotal = $qty * $srate;
        $discountAmount = $grossTotal * ($dis / 100);
        $halfpAmount = $grossTotal * ($halfp / 100);
        
        $taxableAmt = isset($taxableAmtStr) ? (float)$taxableAmtStr : 0;
        if ($taxableAmt > 0) {
            $rowTotal = $taxableAmt;
        } else {
            $rowTotal = $grossTotal - $discountAmount - $halfpAmount;
        }

        // echo "<pre>rowTotal - ";print_r($rowTotal);
        
        $cgstPercent = (float)$cgstStr;
        $sgstPercent = (float)$sgstStr;
        $igstPercent = (float)$igstStr;
        
        if ($cgstPercent == 0 && $sgstPercent == 0) {
            $taxPercent = $igstPercent;
        } else {
            $taxPercent = $cgstPercent + $sgstPercent;
        }
        
        $taxAmount = $rowTotal * ($taxPercent / 100);
        $rowTotal += $taxAmount;
        $purchasePrice = $srate;


        // echo "<pre>totalQty - ";print_r($totalQty);
        // echo "<pre>grossTotal - ";print_r($grossTotal);
        // echo "<pre>discountAmount - ";print_r($discountAmount);
        // echo "<pre>halfpAmount - ";print_r($halfpAmount);
        // echo "<pre>rowTotal - ";print_r($rowTotal);
        // echo "<pre>taxAmount - ";print_r($taxAmount);
        // echo "<pre>purchasePrice - ";print_r($purchasePrice);die;

        return [
            'medicine_name' => $medicineName,
            'medicine_id' => $medicine ? $medicine->id : null,
            'medicines_per_strip' => $medicinesPerStrip,
            'hsn_code' => $hsnStr ?? ($medicine ? $medicine->hsn_code : ''),
            'batch_number' => $batchStr,
            'expiry_date' => $formattedExpiry,
            'quantity' => $totalQty,
            'purchase_price' => $purchasePrice,
            'sale_price' => (float)$mrpStr,
            'item_total' => $rowTotal,
        ];
    }
}