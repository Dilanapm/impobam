<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalePaymentController extends Controller
{
    public function store(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(?:[\.,]\d{1,2})?$/'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:' . now()->toDateString()],
        ], [
            'next_due_date.after_or_equal' => 'La fecha prometida no puede ser anterior a hoy.',
        ]);

        $amountCents = $this->moneyToCents($validated['amount']);

        $result = DB::transaction(function () use ($sale, $amountCents, $validated) {
            $sale = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            $totalCents = $this->moneyToCents($sale->total_amount);
            $paidCents = $this->moneyToCents($sale->payments()->sum('amount'));
            $balanceCents = max($totalCents - $paidCents, 0);

            if ($balanceCents <= 0) {
                return ['ok' => false, 'message' => 'Esta venta ya está pagada.'];
            }

            if ($amountCents > $balanceCents) {
                return ['ok' => false, 'message' => 'El pago no puede ser mayor al saldo.'];
            }

            $newBalanceCents = $balanceCents - $amountCents;

            if ($newBalanceCents > 0 && empty($validated['next_due_date']) && empty($sale->due_date)) {
                return ['ok' => false, 'message' => 'Ingrese la próxima fecha prometida si queda saldo pendiente.'];
            }

            $payment = $sale->payments()->create([
                'amount' => $this->centsToMoney($amountCents),
                'paid_at' => now(),
            ]);

            if ($newBalanceCents <= 0) {
                $sale->update(['due_date' => null]);
            } else {
                $sale->update(['due_date' => $validated['next_due_date'] ?? $sale->due_date]);
            }

            return ['ok' => true, 'paymentId' => $payment->id];
        });

        if (! $result['ok']) {
            return back()->withErrors(['amount' => $result['message']])->withInput();
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Pago registrado correctamente.')
            ->with('receipt_payment_id', $result['paymentId'] ?? null);
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
