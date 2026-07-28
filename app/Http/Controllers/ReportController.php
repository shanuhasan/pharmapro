<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Supplier;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // Common branch ID filter
    private function getBranchId(Request $request)
    {
        return auth()->user()->role === 'admin' ? $request->get('branch_id') : auth()->user()->branch_id;
    }

    // --- 1. SALES REPORT ---
    public function sales(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $supplierId = $request->get('supplier_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        if ($request->ajax()) {
            $query = Sale::with(['branch', 'customer', 'saleItems.stock'])->where('status', 'completed');
            if ($branchId) $query->where('branch_id', $branchId);
            if ($fromDate) $query->whereDate('sale_date', '>=', $fromDate);
            if ($toDate) $query->whereDate('sale_date', '<=', $toDate);
            if ($supplierId) {
                $query->whereHas('saleItems.stock', function($q) use ($supplierId) {
                    $q->whereExists(function($sq) use ($supplierId) {
                        $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                           ->from('purchase_items')
                           ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                           ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                           ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                           ->where('purchases.supplier_id', $supplierId);
                    });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('invoice_number', function($r){
                    return '<a href="'.route('invoice.print', $r->id).'" target="_blank" class="text-blue-600 hover:underline font-bold">'.$r->invoice_number.'</a>';
                })
                ->addColumn('suppliers', function($r){
                    $suppliers = [];
                    foreach($r->saleItems as $item) {
                        if ($item->stock && $item->stock->supplier_name !== 'Unknown') {
                            $suppliers[] = $item->stock->supplier_name;
                        }
                    }
                    $uniqueSuppliers = array_unique($suppliers);
                    return count($uniqueSuppliers) > 0 ? implode(', ', $uniqueSuppliers) : 'Unknown';
                })
                ->editColumn('sale_date', fn($r) => Carbon::parse($r->sale_date)->format('d M Y'))
                ->editColumn('total_amount', fn($r) => setting('currency_symbol', '₹') . ' ' . number_format($r->total_amount, 2))
                ->addColumn('action', function($r){
                    return '
                        <a href="'.route('invoice.print', $r->id).'" target="_blank" class="text-gray-600 hover:text-gray-900 mr-2" title="Print"><i class="fas fa-print"></i></a>
                        <a href="'.route('invoice.pdf', $r->id).'" class="text-red-600 hover:text-red-900 mr-2" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                        <button data-id="'.$r->id.'" class="email-invoice text-blue-600 hover:text-blue-900" title="Email"><i class="fas fa-envelope"></i></button>
                    ';
                })
                ->rawColumns(['action', 'invoice_number'])
                ->make(true);
        }

        // --- Export Logic ---
        if ($request->has('export')) {
            $query = Sale::with(['branch', 'customer', 'saleItems.stock'])->where('status', 'completed');
            if ($branchId) $query->where('branch_id', $branchId);
            if ($fromDate) $query->whereDate('sale_date', '>=', $fromDate);
            if ($toDate) $query->whereDate('sale_date', '<=', $toDate);
            if ($supplierId) {
                $query->whereHas('saleItems.stock', function($q) use ($supplierId) {
                    $q->whereExists(function($sq) use ($supplierId) {
                        $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                           ->from('purchase_items')
                           ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                           ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                           ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                           ->where('purchases.supplier_id', $supplierId);
                    });
                });
            }
            
            $sales = $query->get();
            
            $headers = ['Invoice Number', 'Date', 'Branch', 'Customer', 'Supplier(s)', 'Amount ($)'];
            $rows = [];
            foreach ($sales as $sale) {
                $suppliers = [];
                foreach($sale->saleItems as $item) {
                    if ($item->stock && $item->stock->supplier_name !== 'Unknown') {
                        $suppliers[] = $item->stock->supplier_name;
                    }
                }
                $supplierNames = count($suppliers) > 0 ? implode(', ', array_unique($suppliers)) : 'Unknown';

                $rows[] = [
                    $sale->invoice_number,
                    Carbon::parse($sale->sale_date)->format('d M Y'),
                    $sale->branch->name ?? 'N/A',
                    $sale->customer->name ?? 'Walk-in',
                    $supplierNames,
                    number_format($sale->total_amount, 2)
                ];
            }

            $title = 'Sales Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('sales_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'sales_report.xlsx');
            }
        }

        $branches = Branch::all();
        $suppliers = Supplier::all();
        return view('reports.sales', compact('branches', 'branchId', 'suppliers'));
    }

    // --- 2. PURCHASE REPORT ---
    public function purchases(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $supplierId = $request->get('supplier_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        if ($request->ajax()) {
            $query = Purchase::with(['branch', 'supplier']);
            if ($branchId) $query->where('branch_id', $branchId);
            if ($supplierId) $query->where('supplier_id', $supplierId);
            if ($fromDate) $query->whereDate('purchase_date', '>=', $fromDate);
            if ($toDate) $query->whereDate('purchase_date', '<=', $toDate);

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('purchase_date', fn($r) => Carbon::parse($r->purchase_date)->format('d M Y'))
                ->editColumn('total_amount', fn($r) => setting('currency_symbol', '₹') . ' ' . number_format($r->total_amount, 2))
                ->make(true);
        }

        if ($request->has('export')) {
            $query = Purchase::with(['branch', 'supplier']);
            if ($branchId) $query->where('branch_id', $branchId);
            if ($supplierId) $query->where('supplier_id', $supplierId);
            if ($fromDate) $query->whereDate('purchase_date', '>=', $fromDate);
            if ($toDate) $query->whereDate('purchase_date', '<=', $toDate);
            
            $purchases = $query->get();
            $headers = ['ID', 'Date', 'Branch', 'Supplier', 'Amount (' . setting('currency_symbol', '₹') . ')'];
            $rows = [];
            foreach ($purchases as $p) {
                $rows[] = [
                    $p->id,
                    Carbon::parse($p->purchase_date)->format('d M Y'),
                    $p->branch->name ?? 'N/A',
                    $p->supplier->name ?? 'Unknown',
                    number_format($p->total_amount, 2)
                ];
            }

            $title = 'Purchase Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('purchase_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'purchase_report.xlsx');
            }
        }

        $branches = Branch::all();
        $suppliers = Supplier::all();
        return view('reports.purchases', compact('branches', 'branchId', 'suppliers'));
    }

    // --- 3. STOCK REPORT ---
    public function stock(Request $request)
    {
        $branchId = $this->getBranchId($request);

        if ($request->ajax()) {
            $query = Stock::with(['branch', 'medicine.medicineCategory']);
            if ($branchId) $query->where('branch_id', $branchId);

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('expiry_date', fn($r) => Carbon::parse($r->expiry_date)->format('d M Y'))
                ->make(true);
        }

        if ($request->has('export')) {
            $query = Stock::with(['branch', 'medicine.medicineCategory']);
            if ($branchId) $query->where('branch_id', $branchId);
            
            $stocks = $query->get();
            $headers = ['Medicine', 'Category', 'Branch', 'Batch Number', 'Quantity', 'Expiry Date'];
            $rows = [];
            foreach ($stocks as $s) {
                $rows[] = [
                    $s->medicine->name ?? 'N/A',
                    $s->medicine->medicineCategory->name ?? '-',
                    $s->branch->name ?? 'N/A',
                    $s->batch_number,
                    $s->quantity,
                    Carbon::parse($s->expiry_date)->format('d M Y')
                ];
            }

            $title = 'Current Stock Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('stock_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'stock_report.xlsx');
            }
        }

        $branches = Branch::all();
        return view('reports.stock', compact('branches', 'branchId'));
    }

    // --- 4. EXPIRY REPORT ---
    public function expiry(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $supplierId = $request->get('supplier_id');
        $days = (int) $request->get('days', 30);
        $threshold = Carbon::today()->addDays($days);

        if ($request->ajax()) {
            $query = Stock::with(['branch', 'medicine.medicineCategory'])
                          ->where('quantity', '>', 0)
                          ->where('expiry_date', '<=', $threshold)
                          ->where('expiry_date', '>=', Carbon::today());
            if ($branchId) $query->where('branch_id', $branchId);
            if ($supplierId) {
                $query->whereExists(function($sq) use ($supplierId) {
                    $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                       ->from('purchase_items')
                       ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                       ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                       ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                       ->where('purchases.supplier_id', $supplierId);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('supplier', fn($r) => $r->supplier_name)
                ->editColumn('expiry_date', fn($r) => '<span class="text-red-600 font-bold">' . Carbon::parse($r->expiry_date)->format('d M Y') . '</span>')
                ->rawColumns(['expiry_date'])
                ->make(true);
        }

        if ($request->has('export')) {
            $query = Stock::with(['branch', 'medicine.medicineCategory'])
                          ->where('quantity', '>', 0)
                          ->where('expiry_date', '<=', $threshold)
                          ->where('expiry_date', '>=', Carbon::today());
            if ($branchId) $query->where('branch_id', $branchId);
            if ($supplierId) {
                $query->whereExists(function($sq) use ($supplierId) {
                    $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                       ->from('purchase_items')
                       ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                       ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                       ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                       ->where('purchases.supplier_id', $supplierId);
                });
            }
            
            $stocks = $query->get();
            $headers = ['Medicine', 'Supplier', 'Branch', 'Batch', 'Qty', 'Expiry Date'];
            $rows = [];
            foreach ($stocks as $s) {
                $rows[] = [
                    $s->medicine->name,
                    $s->supplier_name,
                    $s->branch->name ?? 'N/A',
                    $s->batch_number,
                    $s->quantity,
                    Carbon::parse($s->expiry_date)->format('d M Y')
                ];
            }
            $title = 'Expiry Report (' . $days . ' Days)';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('expiry_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'expiry_report.xlsx');
            }
        }

        $branches = Branch::all();
        $suppliers = Supplier::all();
        return view('reports.expiry', compact('branches', 'branchId', 'suppliers'));
    }

    // --- 5. CUSTOMER REPORT ---
    public function customers(Request $request)
    {
        if ($request->ajax()) {
            $query = Customer::withCount('sales')->withSum('sales', 'total_amount');
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('total_sales', fn($r) => $r->sales_count)
                ->editColumn('sales_sum_total_amount', fn($r) => setting('currency_symbol', '₹') . ' ' . number_format($r->sales_sum_total_amount ?? 0, 2))
                ->make(true);
        }

        if ($request->has('export')) {
            $customers = Customer::withCount('sales')->withSum('sales', 'total_amount')->get()->sortByDesc('sales_sum_total_amount');
            $headers = ['Customer Name', 'Phone', 'Total Purchases', 'Total Spent (' . setting('currency_symbol', '₹') . ')'];
            $rows = [];
            foreach ($customers as $c) {
                $rows[] = [
                    $c->name,
                    $c->phone,
                    $c->sales_count,
                    number_format($c->sales_sum_total_amount ?? 0, 2)
                ];
            }
            $title = 'Top Customers Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('customers_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'customers_report.xlsx');
            }
        }

        return view('reports.customers');
    }

    // --- 6. PROFIT/LOSS REPORT ---
    public function profit(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $supplierId = $request->get('supplier_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        if ($request->ajax()) {
            $query = SaleItem::with(['sale.branch', 'medicine', 'stock'])
                             ->whereHas('sale', function($q) use ($branchId, $fromDate, $toDate) {
                                 $q->where('status', 'completed');
                                 if ($branchId) $q->where('branch_id', $branchId);
                                 if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                                 if ($toDate) $q->whereDate('sale_date', '<=', $toDate);
                             });
            if ($supplierId) {
                $query->whereHas('stock', function($q) use ($supplierId) {
                    $q->whereExists(function($sq) use ($supplierId) {
                        $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                           ->from('purchase_items')
                           ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                           ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                           ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                           ->where('purchases.supplier_id', $supplierId);
                    });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('sale_date', fn($r) => Carbon::parse($r->sale->sale_date)->format('d M Y'))
                ->addColumn('supplier', fn($r) => $r->stock ? $r->stock->supplier_name : 'Unknown')
                ->addColumn('purchase_cost', fn($r) => $r->stock ? ($r->stock->purchase_price * $r->quantity) : 0)
                ->addColumn('sale_revenue', fn($r) => $r->total)
                ->addColumn('profit', function($r){
                    $cost = $r->stock ? ($r->stock->purchase_price * $r->quantity) : 0;
                    $profit = $r->total - $cost;
                    return '<span class="'.($profit >= 0 ? 'text-green-600' : 'text-red-600').' font-bold">'.setting('currency_symbol', '₹').' '.number_format($profit, 2).'</span>';
                })
                ->rawColumns(['profit'])
                ->make(true);
        }

        if ($request->has('export')) {
            $query = SaleItem::with(['sale.branch', 'medicine', 'stock'])
                             ->whereHas('sale', function($q) use ($branchId, $fromDate, $toDate) {
                                 $q->where('status', 'completed');
                                 if ($branchId) $q->where('branch_id', $branchId);
                                 if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                                 if ($toDate) $q->whereDate('sale_date', '<=', $toDate);
                             });
            if ($supplierId) {
                $query->whereHas('stock', function($q) use ($supplierId) {
                    $q->whereExists(function($sq) use ($supplierId) {
                        $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                           ->from('purchase_items')
                           ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                           ->whereColumn('purchase_items.medicine_id', 'stock.medicine_id')
                           ->whereColumn('purchase_items.batch_number', 'stock.batch_number')
                           ->where('purchases.supplier_id', $supplierId);
                    });
                });
            }
            
            $items = $query->get();
            $headers = ['Date', 'Invoice', 'Medicine', 'Supplier', 'Qty', 'Total Cost (' . setting('currency_symbol', '₹') . ')', 'Total Revenue (' . setting('currency_symbol', '₹') . ')', 'Net Profit (' . setting('currency_symbol', '₹') . ')'];
            $rows = [];
            
            foreach ($items as $r) {
                $cost = $r->stock ? ($r->stock->purchase_price * $r->quantity) : 0;
                $profit = $r->total - $cost;
                $supplier = $r->stock ? $r->stock->supplier_name : 'Unknown';
                
                $rows[] = [
                    Carbon::parse($r->sale->sale_date)->format('d M Y'),
                    $r->sale->invoice_number,
                    $r->medicine->name,
                    $supplier,
                    $r->quantity,
                    number_format($cost, 2),
                    number_format($r->total, 2),
                    number_format($profit, 2)
                ];
            }

            $title = 'Profit and Loss Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('profit_loss_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'profit_loss_report.xlsx');
            }
        }

        $branches = Branch::all();
        $suppliers = Supplier::all();
        return view('reports.profit', compact('branches', 'branchId', 'suppliers'));
    }

    // --- 7. MEDICINE-WISE SALES ---
    public function medicineSales(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        if ($request->ajax()) {
            $query = SaleItem::select('medicine_id', \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(total) as total_revenue'))
                             ->with('medicine.medicineCategory')
                             ->whereHas('sale', function($q) use ($branchId, $fromDate, $toDate) {
                                 $q->where('status', 'completed');
                                 if ($branchId) $q->where('branch_id', $branchId);
                                 if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                                 if ($toDate) $q->whereDate('sale_date', '<=', $toDate);
                             })
                             ->groupBy('medicine_id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('total_qty', fn($r) => $r->total_qty)
                ->editColumn('total_revenue', fn($r) => setting('currency_symbol', '₹') . ' ' . number_format($r->total_revenue, 2))
                ->make(true);
        }

        if ($request->has('export')) {
            $query = SaleItem::select('medicine_id', \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(total) as total_revenue'))
                             ->with('medicine.medicineCategory')
                             ->whereHas('sale', function($q) use ($branchId, $fromDate, $toDate) {
                                 $q->where('status', 'completed');
                                 if ($branchId) $q->where('branch_id', $branchId);
                                 if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                                 if ($toDate) $q->whereDate('sale_date', '<=', $toDate);
                             })
                             ->groupBy('medicine_id');
            
            $items = $query->get();
            $headers = ['Medicine', 'Category', 'Total Qty Sold', 'Total Revenue Generated (' . setting('currency_symbol', '₹') . ')'];
            $rows = [];
            foreach ($items as $r) {
                $rows[] = [
                    $r->medicine->name,
                    $r->medicine->medicineCategory->name ?? '-',
                    $r->total_qty,
                    number_format($r->total_revenue, 2)
                ];
            }

            $title = 'Medicine-wise Sales Report';
            if ($request->export == 'pdf') {
                $pdf = Pdf::loadView('reports.exports.table', compact('title', 'headers', 'rows'));
                return $pdf->download('medicine_sales_report.pdf');
            } elseif ($request->export == 'excel') {
                return Excel::download(new \App\Exports\SalesExport($title, $headers, $rows), 'medicine_sales_report.xlsx');
            }
        }

        $branches = Branch::all();
        return view('reports.medicine_sales', compact('branches', 'branchId'));
    }
}
