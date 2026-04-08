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

   Route::get('/dashboard/diag/image-support', function () {
       $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

       $gdInfo = function_exists('gd_info') ? gd_info() : null;
       $canUseTtf = function_exists('imagettftext')
           && function_exists('imagettfbbox')
           && is_file($fontPath);

       $smoke = null;

       try {
           if (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
               $img = imagecreatetruecolor(40, 20);
               $bg = imagecolorallocate($img, 255, 255, 255);
               imagefill($img, 0, 0, $bg);

               if ($canUseTtf) {
                   $black = imagecolorallocate($img, 0, 0, 0);
                   imagettftext($img, 10, 0, 2, 14, $black, $fontPath, 'OK');
               }

               ob_start();
               imagepng($img);
               $raw = ob_get_clean();
               imagedestroy($img);

               $smoke = is_string($raw) && strlen($raw) > 0;
           }
       } catch (Throwable) {
           $smoke = false;
       }

       return response()->json([
           'php' => PHP_VERSION,
           'gd_extension_loaded' => extension_loaded('gd'),
           'gd_info' => $gdInfo,
           'functions' => [
               'imagecreatetruecolor' => function_exists('imagecreatetruecolor'),
               'imagepng' => function_exists('imagepng'),
               'imagettftext' => function_exists('imagettftext'),
               'imagettfbbox' => function_exists('imagettfbbox'),
               'iconv' => function_exists('iconv'),
           ],
           'font' => [
               'path' => $fontPath,
               'exists' => is_file($fontPath),
               'readable' => is_readable($fontPath),
           ],
           'can_use_ttf' => $canUseTtf,
           'png_smoke_test_ok' => $smoke,
       ]);
   })->name('admin.diag.image-support');

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
