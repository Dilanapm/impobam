<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
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

        $outputsQuery = StockOutput::with('product')
            ->when($productId, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('moved_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('moved_at', '<=', $endDate);
            })
            ->orderByDesc('moved_at')
            ->orderByDesc('id');

        $outputs = (clone $outputsQuery)
            ->paginate(15)
            ->withQueryString();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $totalRecords = StockOutput::count();
        $totalUnits = StockOutput::sum('quantity');
        $todayRecords = StockOutput::whereDate('moved_at', today())->count();
        $todayUnits = StockOutput::whereDate('moved_at', today())->sum('quantity');

        return view('admin.dashboard', compact(
            'outputs',
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
}