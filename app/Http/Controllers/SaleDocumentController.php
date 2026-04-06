<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SaleDocumentController extends Controller
{
    public function saleNote(Sale $sale): Response
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
        $generatedAt = now();

        $pdf = Pdf::loadView('sales.documents.sale-note', [
            'sale' => $sale,
            'totalCents' => $totalCents,
            'paidCents' => $paidCents,
            'balanceCents' => $balanceCents,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('nota-venta-' . $sale->id . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    public function paymentReceipt(Sale $sale, SalePayment $payment): Response
    {
        if ((int) $payment->sale_id !== (int) $sale->id) {
            abort(404);
        }

        $sale->load([
            'items.product',
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $totalCents = $this->moneyToCents($sale->total_amount);
        $paidCents = 0;

        foreach ($sale->payments as $row) {
            $paidCents += $this->moneyToCents($row->amount);
        }

        $balanceCents = max($totalCents - $paidCents, 0);
        $generatedAt = now();

        $pdf = Pdf::loadView('sales.documents.payment-receipt', [
            'sale' => $sale,
            'payment' => $payment,
            'totalCents' => $totalCents,
            'paidCents' => $paidCents,
            'balanceCents' => $balanceCents,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('recibo-pago-venta-' . $sale->id . '-pago-' . $payment->id . '-' . $generatedAt->format('Y-m-d') . '.pdf');
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
