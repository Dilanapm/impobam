<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockOutput;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $salesLoadError = null;

        try {
            $sales = Sale::query()
                ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
                ->withSum('payments', 'amount')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'sales_page')
                ->withQueryString();

            $sales->getCollection()->transform(function (Sale $sale) {
                $totalCents = $this->moneyToCents($sale->total_amount);
                $paidCents = $this->moneyToCents($sale->payments_sum_amount ?? 0);
                $balanceCents = max($totalCents - $paidCents, 0);

                return [
                    'sale' => $sale,
                    'totalCents' => $totalCents,
                    'paidCents' => $paidCents,
                    'balanceCents' => $balanceCents,
                ];
            });
        } catch (QueryException) {
            $salesLoadError = 'No se pudieron cargar las ventas. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.';
            $sales = new LengthAwarePaginator(
                [],
                0,
                15,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                    'pageName' => 'sales_page',
                ]
            );
        }

        return view('admin.dashboard', compact(
            'outputs',
            'products',
            'totalRecords',
            'totalUnits',
            'todayRecords',
            'todayUnits',
            'sales',
            'salesLoadError'
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

    private function moneyToCents(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = str_replace(',', '.', (string) $value);

        if (! str_contains($normalized, '.')) {
            return ((int) $normalized) * 100;
        }

        [$whole, $decimals] = explode('.', $normalized, 2);
        $wholePart = (int) $whole;
        $decimalPart = (int) str_pad(substr($decimals, 0, 2), 2, '0');

        return ($wholePart * 100) + $decimalPart;
    }
}