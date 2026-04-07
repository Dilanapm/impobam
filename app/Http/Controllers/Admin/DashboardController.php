<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockOutput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $totalRecords = StockOutput::count();
        $totalUnits = StockOutput::sum('quantity');
        $todayRecords = StockOutput::whereDate('moved_at', today())->count();
        $todayUnits = StockOutput::whereDate('moved_at', today())->sum('quantity');

        return view('admin.dashboard', compact(
            'products',
            'totalRecords',
            'totalUnits',
            'todayRecords',
            'todayUnits'
        ));
    }

    public function destroy(StockOutput $stockOutput): RedirectResponse
    {
        $stockOutput->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Registro eliminado correctamente.');
    }

    public function destroySale(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Venta eliminada correctamente.');
    }

}
