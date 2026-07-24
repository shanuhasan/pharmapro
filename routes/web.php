<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Branches
    Route::resource('branches', \App\Http\Controllers\BranchController::class)->middleware('role:admin');
    
    // Medicines Management
    Route::resource('medicine-categories', \App\Http\Controllers\MedicineCategoryController::class);
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::resource('medicines', \App\Http\Controllers\MedicineController::class);
    
    // Suppliers
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    
    // Customers
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    
    // Purchases
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);
    
    // Stock
    Route::get('stock', [\App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
    Route::post('stock/{stock}/adjust', [\App\Http\Controllers\StockController::class, 'adjust'])->name('stock.adjust');
    Route::post('stock/{stock}/transfer', [\App\Http\Controllers\StockController::class, 'transfer'])->name('stock.transfer');

    // POS API
    Route::get('api/medicine/search', [\App\Http\Controllers\PosController::class, 'searchMedicine']);
    Route::get('api/stock/check', [\App\Http\Controllers\PosController::class, 'checkStock']);
    Route::get('api/customer/search', [\App\Http\Controllers\PosController::class, 'searchCustomer']);

    // POS & Sales
    Route::get('pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::resource('sales', \App\Http\Controllers\SaleController::class);

    // Expenses
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);

    // Returns
    Route::get('returns/purchase', [\App\Http\Controllers\ReturnController::class, 'createPurchaseReturn'])->name('returns.purchase.create');
    Route::post('returns/purchase', [\App\Http\Controllers\ReturnController::class, 'storePurchaseReturn'])->name('returns.purchase.store');
    Route::get('api/purchase/{id}/items', [\App\Http\Controllers\ReturnController::class, 'getPurchaseItems']);

    Route::get('returns/sale', [\App\Http\Controllers\ReturnController::class, 'createSaleReturn'])->name('returns.sale.create');
    Route::post('returns/sale', [\App\Http\Controllers\ReturnController::class, 'storeSaleReturn'])->name('returns.sale.store');
    Route::get('api/sale/{invoice_number}/items', [\App\Http\Controllers\ReturnController::class, 'getSaleItems']);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('purchases', [\App\Http\Controllers\ReportController::class, 'purchases'])->name('purchases');
        Route::get('stock', [\App\Http\Controllers\ReportController::class, 'stock'])->name('stock');
        Route::get('expiry', [\App\Http\Controllers\ReportController::class, 'expiry'])->name('expiry');
        Route::get('customers', [\App\Http\Controllers\ReportController::class, 'customers'])->name('customers');
        Route::get('profit', [\App\Http\Controllers\ReportController::class, 'profit'])->name('profit');
        Route::get('medicine-sales', [\App\Http\Controllers\ReportController::class, 'medicineSales'])->name('medicine_sales');
    });

    // Settings
    Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\SettingController::class, 'store'])->name('settings.store');

    // Users
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });

    // Invoices
    Route::get('invoice/{sale}/pdf', [\App\Http\Controllers\InvoiceController::class, 'pdf'])->name('invoice.pdf');
    Route::get('invoice/{sale}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoice.print');
    Route::post('invoice/{sale}/email', [\App\Http\Controllers\InvoiceController::class, 'email'])->name('invoice.email');
});

require __DIR__.'/auth.php';
