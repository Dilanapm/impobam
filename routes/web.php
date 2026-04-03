<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockOutputController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePaymentController;

Route::view('/', 'home')->name('home');

Route::get('/salidas', [StockOutputController::class, 'create'])->name('stock-outputs.create');
Route::post('/salidas', [StockOutputController::class, 'store'])->name('stock-outputs.store');
Route::get('/ventas', [SaleController::class, 'create'])->name('sales.create');
Route::post('/ventas', [SaleController::class, 'store'])->name('sales.store');
Route::get('/ventas/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::post('/ventas/{sale}/pagos', [SalePaymentController::class, 'store'])->name('sales.payments.store');
Route::get('/creditos', [SaleController::class, 'credits'])->name('credits.index');
Route::delete('/salidas/{stockOutput}', [StockOutputController::class, 'destroyOwn'])
    ->name('stock-outputs.employee-destroy')
    ->middleware('signed');
    
Route::middleware('auth')->group(function () {
   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
   Route::delete('/dashboard/salidas/{stockOutput}', [DashboardController::class, 'destroy'])
        ->name('admin.stock-outputs.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
