<?php

namespace App\Services;

use App\Models\Stock;

class StockService
{
    /**
     * Add stock for a given branch and item details.
     *
     * @param int $branchId
     * @param array $item
     * @return \App\Models\Stock
     */
    public function addStock(int $branchId, array $item)
    {
        // Try to find existing stock by branch, medicine, and batch number
        $stock = Stock::where('branch_id', $branchId)
            ->where('medicine_id', $item['medicine_id'])
            ->where('batch_number', $item['batch_number'])
            ->first();

        if ($stock) {
            // If the same batch already exists, add the quantity
            $stock->quantity += $item['quantity'];
            // Optionally update prices if they changed (or keep original)
            $stock->purchase_price = $item['purchase_price'];
            $stock->sale_price = $item['sale_price'];
            $stock->expiry_date = $item['expiry_date'] ?? $stock->expiry_date;
            $stock->save();
        } else {
            // New batch, create new record
            $stock = Stock::create([
                'branch_id' => $branchId,
                'medicine_id' => $item['medicine_id'],
                'batch_number' => $item['batch_number'],
                'expiry_date' => $item['expiry_date'] ?? null,
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'sale_price' => $item['sale_price'],
            ]);
        }

        return $stock;
    }
}
