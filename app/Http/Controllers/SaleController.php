<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('credits.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'delivery_location' => ['nullable', 'string', 'max:255'],
            'payment_type' => ['nullable', 'in:cash,credit'],
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

        $paymentType = $validated['payment_type'] ?? null;
        $initialPaymentCents = $paymentType === 'cash'
            ? $totalCents
            : $this->moneyToCents($validated['initial_payment_amount'] ?? 0);

        if ($initialPaymentCents > $totalCents) {
            return back()
                ->withInput()
                ->withErrors(['initial_payment_amount' => 'El pago inicial no puede ser mayor al total.']);
        }

        if ($paymentType !== 'cash' && $initialPaymentCents > 0 && $initialPaymentCents < $totalCents && empty($validated['due_date'])) {
            return back()
                ->withInput()
                ->withErrors(['due_date' => 'Ingrese una fecha prometida si queda saldo pendiente y hubo un pago inicial.']);
        }

        $sale = DB::transaction(function () use ($validated, $normalizedItems, $totalCents, $initialPaymentCents, $paymentType) {
            $dueDate = $paymentType === 'cash'
                ? null
                : ($validated['due_date'] ?? null);

            $sale = Sale::create([
                'customer_name' => $validated['customer_name'],
                'delivery_location' => $validated['delivery_location'] ?? null,
                'due_date' => $dueDate,
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

    public function show(Request $request, Sale $sale): View
    {
        $sale->load([
            'items.product',
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $paymentOnly = $request->boolean('payment_only');

        $totalCents = $this->moneyToCents($sale->total_amount);
        $paidCents = 0;

        foreach ($sale->payments as $payment) {
            $paidCents += $this->moneyToCents($payment->amount);
        }

        $balanceCents = max($totalCents - $paidCents, 0);

        return view('sales.show', compact('sale', 'totalCents', 'paidCents', 'balanceCents', 'paymentOnly'));
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
