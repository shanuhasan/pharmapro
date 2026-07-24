<?php

namespace App\Services;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate PDF for a given sale.
     *
     * @param int|Sale $sale
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate($sale)
    {
        if (!$sale instanceof Sale) {
            $sale = Sale::findOrFail($sale);
        }

        $sale->load(['saleItems.medicine', 'customer', 'branch', 'user']);

        // You can set paper size, orientation etc.
        $pdf = Pdf::loadView('invoices.template', compact('sale'))
                  ->setPaper('a4', 'portrait');

        return $pdf;
    }
}
