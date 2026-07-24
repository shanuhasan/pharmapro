<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\InvoiceService;
use App\Mail\SaleInvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function pdf(Sale $sale)
    {
        $pdf = $this->invoiceService->generate($sale);
        return $pdf->download('Invoice-' . $sale->invoice_number . '.pdf');
    }

    public function print(Sale $sale)
    {
        $sale->load(['saleItems.medicine', 'customer', 'branch', 'user']);
        return view('invoices.print', compact('sale'));
    }

    public function email(Request $request, Sale $sale)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $pdf = $this->invoiceService->generate($sale);
            
            Mail::to($request->email)->send(new SaleInvoiceMail($sale, $pdf->output()));

            return back()->with('success', 'Invoice emailed successfully to ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
