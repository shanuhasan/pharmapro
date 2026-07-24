<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Stock;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->role === 'admin' ? null : auth()->user()->branch_id;
        $today = Carbon::today();

        // 1. Today's Sales
        $salesQuery = Sale::whereDate('sale_date', $today)->where('status', 'completed');
        if ($branchId) $salesQuery->where('branch_id', $branchId);
        $todaySalesAmount = $salesQuery->sum('total_amount');
        $todaySalesCount = $salesQuery->count();

        // 2. Today's Purchases
        $purchaseQuery = Purchase::whereDate('purchase_date', $today);
        if ($branchId) $purchaseQuery->where('branch_id', $branchId);
        $todayPurchasesAmount = $purchaseQuery->sum('total_amount');

        // 3. Low Stock Alerts
        $threshold = (int) setting('low_stock_threshold', 10);
        $lowStockQuery = Stock::where('quantity', '<', $threshold)->where('quantity', '>', 0);
        if ($branchId) $lowStockQuery->where('branch_id', $branchId);
        $lowStockCount = $lowStockQuery->count();

        // 4. Expiring Soon Count
        $thirtyDaysFromNow = Carbon::today()->addDays(30);
        $expiringQuery = Stock::where('quantity', '>', 0)
                              ->where('expiry_date', '<=', $thirtyDaysFromNow)
                              ->where('expiry_date', '>=', $today);
        if ($branchId) $expiringQuery->where('branch_id', $branchId);
        $expiringSoonCount = $expiringQuery->count();

        // 5. Monthly Revenue Chart Data
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $daysInMonth = Carbon::now()->daysInMonth;
        
        $chartLabels = [];
        $chartData = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = Carbon::createFromDate($currentYear, $currentMonth, $i)->format('Y-m-d');
            $chartLabels[] = $i; // just day number
            
            $daySaleQuery = Sale::whereDate('sale_date', $dateStr)->where('status', 'completed');
            if ($branchId) $daySaleQuery->where('branch_id', $branchId);
            $chartData[] = $daySaleQuery->sum('total_amount');
        }

        return view('dashboard', compact(
            'todaySalesAmount', 
            'todaySalesCount', 
            'todayPurchasesAmount', 
            'lowStockCount', 
            'expiringSoonCount',
            'chartLabels',
            'chartData'
        ));
    }
}
