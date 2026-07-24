<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    // --- PURCHASE RETURNS ---

    public function createPurchaseReturn()
    {
        $purchases = Purchase::with('supplier')->orderBy('id', 'desc')->get();
        return view('returns.purchase.create', compact('purchases'));
    }

    public function getPurchaseItems($id)
    {
        $purchase = Purchase::with('purchaseItems.medicine')->findOrFail($id);
        return response()->json($purchase);
    }

    public function storePurchaseReturn(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_items,id',
            'items.*.return_qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::with('purchaseItems')->findOrFail($request->purchase_id);
            $totalAmount = 0;

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'branch_id' => $purchase->branch_id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => date('Y-m-d'),
                'total_amount' => 0, // Will update later
                'reason' => $request->reason,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $reqItem) {
                $purchaseItem = $purchase->purchaseItems->where('id', $reqItem['item_id'])->first();
                if (!$purchaseItem || $reqItem['return_qty'] > $purchaseItem->quantity) {
                    throw new \Exception("Invalid return quantity for item ID " . $reqItem['item_id']);
                }

                $returnPrice = $purchaseItem->purchase_price;
                $itemTotal = $returnPrice * $reqItem['return_qty'];
                $totalAmount += $itemTotal;

                PurchaseReturnItem::create([
                    'return_id' => $purchaseReturn->id,
                    'medicine_id' => $purchaseItem->medicine_id,
                    'batch_number' => $purchaseItem->batch_number,
                    'quantity' => $reqItem['return_qty'],
                    'price' => $returnPrice,
                    'total' => $itemTotal,
                ]);

                // Deduct from stock
                $stock = Stock::where('medicine_id', $purchaseItem->medicine_id)
                              ->where('branch_id', $purchase->branch_id)
                              ->where('batch_number', $purchaseItem->batch_number)
                              ->first();

                if ($stock) {
                    $oldQty = $stock->quantity;
                    $stock->quantity -= $reqItem['return_qty'];
                    if ($stock->quantity < 0) $stock->quantity = 0;
                    $stock->save();

                    StockLog::create([
                        'stock_id' => $stock->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjustment',
                        'quantity_changed' => -$reqItem['return_qty'],
                        'previous_quantity' => $oldQty,
                        'new_quantity' => $stock->quantity,
                        'reason' => "Purchase Return PR-" . $purchaseReturn->id,
                        'reference_id' => $purchaseReturn->id,
                    ]);
                }
            }

            $purchaseReturn->update(['total_amount' => $totalAmount]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase Return processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- SALE RETURNS ---

    public function createSaleReturn()
    {
        return view('returns.sale.create');
    }

    public function getSaleItems($invoice_number)
    {
        $sale = Sale::with(['saleItems.medicine', 'customer'])->where('invoice_number', $invoice_number)->first();
        if (!$sale) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        return response()->json($sale);
    }

    public function storeSaleReturn(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:sale_items,id',
            'items.*.return_qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sale = Sale::with('saleItems')->findOrFail($request->sale_id);
            $totalAmount = 0;

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'customer_id' => $sale->customer_id,
                'return_date' => date('Y-m-d'),
                'total_amount' => 0, // Update later
                'reason' => $request->reason,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $reqItem) {
                $saleItem = $sale->saleItems->where('id', $reqItem['item_id'])->first();
                if (!$saleItem || $reqItem['return_qty'] > $saleItem->quantity) {
                    throw new \Exception("Invalid return quantity for item ID " . $reqItem['item_id']);
                }

                $returnPrice = $saleItem->sale_price;
                $itemTotal = $returnPrice * $reqItem['return_qty'];
                $totalAmount += $itemTotal;

                SaleReturnItem::create([
                    'return_id' => $saleReturn->id,
                    'medicine_id' => $saleItem->medicine_id,
                    'quantity' => $reqItem['return_qty'],
                    'price' => $returnPrice,
                    'total' => $itemTotal,
                ]);

                // Add back to active stock
                $stock = Stock::find($saleItem->stock_id);
                if ($stock) {
                    $oldQty = $stock->quantity;
                    $stock->quantity += $reqItem['return_qty'];
                    $stock->save();

                    StockLog::create([
                        'stock_id' => $stock->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjustment',
                        'quantity_changed' => $reqItem['return_qty'],
                        'previous_quantity' => $oldQty,
                        'new_quantity' => $stock->quantity,
                        'reason' => "Sale Return SR-" . $saleReturn->id,
                        'reference_id' => $saleReturn->id,
                    ]);
                }
            }

            $saleReturn->update(['total_amount' => $totalAmount]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sale Return processed successfully. Refund: $' . number_format($totalAmount, 2)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
