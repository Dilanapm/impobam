<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function create(): View
    {
        return view('sales.create');
    }

    public function credits(): View
    {
        $pendingSales = [];
        $creditsLoadError = null;

        try {
            $sales = Sale::query()
                ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
                ->withSum('payments', 'amount')
                ->orderBy('due_date')
                ->orderByDesc('id')
                ->get();

            foreach ($sales as $sale) {
                $totalCents = $this->moneyToCents($sale->total_amount);
                $paidCents = $this->moneyToCents($sale->payments_sum_amount ?? 0);
                $balanceCents = max($totalCents - $paidCents, 0);

                if ($balanceCents <= 0) {
                    continue;
                }

                $pendingSales[] = [
                    'sale' => $sale,
                    'totalCents' => $totalCents,
                    'paidCents' => $paidCents,
                    'balanceCents' => $balanceCents,
                ];
            }
        } catch (QueryException) {
            $creditsLoadError = 'No se pudieron cargar los créditos. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.';
        }

        return view('credits.index', compact('pendingSales', 'creditsLoadError'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'delivery_location' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date', 'after_or_equal:' . now()->toDateString()],
            'initial_payment_amount' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(?:[\.,]\d{1,2})?$/'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(?:[\.,]\d{1,2})?$/'],
        ], [
            'due_date.after_or_equal' => 'La fecha prometida no puede ser anterior a hoy.',
        ]);

        $items = $validated['items'];

        $totalCents = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unitPriceCents = $this->moneyToCents($item['unit_price']);
            $lineTotalCents = $quantity * $unitPriceCents;

            $totalCents += $lineTotalCents;

            $normalizedItems[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $this->centsToMoney($unitPriceCents),
                'line_total' => $this->centsToMoney($lineTotalCents),
            ];
        }

        if ($totalCents <= 0) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'La venta debe tener un total mayor a 0.']);
        }

        $initialPaymentCents = $this->moneyToCents($validated['initial_payment_amount'] ?? 0);

        if ($initialPaymentCents > $totalCents) {
            return back()
                ->withInput()
                ->withErrors(['initial_payment_amount' => 'El pago inicial no puede ser mayor al total.']);
        }

        if ($initialPaymentCents > 0 && $initialPaymentCents < $totalCents && empty($validated['due_date'])) {
            return back()
                ->withInput()
                ->withErrors(['due_date' => 'Ingrese una fecha prometida si queda saldo pendiente y hubo un pago inicial.']);
        }

        $sale = DB::transaction(function () use ($validated, $normalizedItems, $totalCents, $initialPaymentCents) {
            $sale = Sale::create([
                'customer_name' => $validated['customer_name'],
                'delivery_location' => $validated['delivery_location'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'total_amount' => $this->centsToMoney($totalCents),
            ]);

            $sale->items()->createMany($normalizedItems);

            if ($initialPaymentCents > 0) {
                $sale->payments()->create([
                    'amount' => $this->centsToMoney($initialPaymentCents),
                    'paid_at' => now(),
                ]);
            }

            if ($initialPaymentCents >= $totalCents) {
                $sale->update(['due_date' => null]);
            }

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale): View
    {
        $sale->load([
            'items.product',
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $totalCents = $this->moneyToCents($sale->total_amount);
        $paidCents = 0;

        foreach ($sale->payments as $payment) {
            $paidCents += $this->moneyToCents($payment->amount);
        }

        $balanceCents = max($totalCents - $paidCents, 0);

        return view('sales.show', compact('sale', 'totalCents', 'paidCents', 'balanceCents'));
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

    private function centsToMoney(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
