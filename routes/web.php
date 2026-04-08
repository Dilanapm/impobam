<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockOutputController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleDocumentController;
use App\Http\Controllers\SalePaymentController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/salidas', [StockOutputController::class, 'create'])->name('stock-outputs.create');
Route::post('/salidas', [StockOutputController::class, 'store'])->name('stock-outputs.store');
Route::get('/ventas', [SaleController::class, 'create'])->name('sales.create');
Route::post('/ventas', [SaleController::class, 'store'])->name('sales.store');
Route::get('/ventas/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::get('/ventas/{sale}/nota', [SaleDocumentController::class, 'saleNote'])->name('sales.note');
Route::get('/ventas/{sale}/nota/imagen', [SaleDocumentController::class, 'saleNoteImage'])->name('sales.note.image');
Route::post('/ventas/{sale}/pagos', [SalePaymentController::class, 'store'])->name('sales.payments.store');
Route::get('/ventas/{sale}/pagos/{payment}/recibo', [SaleDocumentController::class, 'paymentReceipt'])->name('sales.payments.receipt');
Route::get('/creditos', [SaleController::class, 'credits'])->name('credits.index');
Route::delete('/salidas/{stockOutput}', [StockOutputController::class, 'destroyOwn'])
    ->name('stock-outputs.employee-destroy')
    ->middleware('signed');
    
Route::middleware('auth')->group(function () {
   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

   Route::get('/dashboard/reportes/ventas', [ReportController::class, 'sales'])
       ->name('admin.reports.sales');
   Route::get('/dashboard/reportes/salidas', [ReportController::class, 'outputs'])
       ->name('admin.reports.outputs');
   Route::get('/dashboard/reportes/pagos-pendientes', [ReportController::class, 'pendingPayments'])
       ->name('admin.reports.pending-payments');

   Route::delete('/dashboard/salidas/{stockOutput}', [DashboardController::class, 'destroy'])
        ->name('admin.stock-outputs.destroy');
   Route::delete('/dashboard/ventas/{sale}', [DashboardController::class, 'destroySale'])
       ->name('admin.sales.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
