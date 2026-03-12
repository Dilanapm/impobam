<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockOutputController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', [StockOutputController::class, 'create'])->name('stock-outputs.create');
Route::post('/salidas', [StockOutputController::class, 'store'])->name('stock-outputs.store');
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
