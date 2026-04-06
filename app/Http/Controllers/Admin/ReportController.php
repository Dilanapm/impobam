<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\StockOutput;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Throwable;

class ReportController extends Controller
{
    public function sales(Request $request): Response|RedirectResponse
    {
        $range = $this->resolveRange($request);

        try {
            $sales = Sale::query()
                ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
                ->withSum('payments', 'amount')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
        } catch (QueryException) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'No se pudo generar el reporte de ventas. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.');
        }

        $rows = $sales->map(function (Sale $sale) {
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

        $soldCents = $rows->sum(fn (array $row) => $row['totalCents']);
        $paidCents = $rows->sum(fn (array $row) => $row['paidCents']);
        $balanceCents = $rows->sum(fn (array $row) => $row['balanceCents']);

        $cashCount = $rows->filter(fn (array $row) => $row['balanceCents'] <= 0)->count();
        $creditCount = $rows->filter(fn (array $row) => $row['balanceCents'] > 0)->count();

        $summary = [
            'count' => $rows->count(),
            'cashCount' => $cashCount,
            'creditCount' => $creditCount,
            'soldCents' => $soldCents,
            'paidCents' => $paidCents,
            'balanceCents' => $balanceCents,
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('admin.reports.sales', [
            'rows' => $rows,
            'summary' => $summary,
            'range' => $range,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-ventas-' . $range['key'] . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    public function outputs(Request $request): Response|RedirectResponse
    {
        $range = $this->resolveRange($request);

        try {
            $outputs = StockOutput::query()
                ->with('product')
                ->select(['id', 'product_id', 'quantity', 'employee_name', 'notes', 'moved_at'])
                ->whereBetween('moved_at', [$range['start'], $range['end']])
                ->orderByDesc('moved_at')
                ->orderByDesc('id')
                ->get();
        } catch (QueryException) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'No se pudo generar el reporte de salidas. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.');
        }

        $summary = [
            'count' => $outputs->count(),
            'units' => (int) $outputs->sum('quantity'),
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('admin.reports.outputs', [
            'outputs' => $outputs,
            'summary' => $summary,
            'range' => $range,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-salidas-' . $range['key'] . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    public function pendingPayments(Request $request): Response|RedirectResponse
    {
        $range = $this->resolveRange($request);

        try {
            $sales = Sale::query()
                ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
                ->withSum('payments', 'amount')
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
        } catch (QueryException) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'No se pudo generar el reporte de pagos pendientes. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.');
        }

        $rows = $sales->map(function (Sale $sale) {
            $totalCents = $this->moneyToCents($sale->total_amount);
            $paidCents = $this->moneyToCents($sale->payments_sum_amount ?? 0);
            $balanceCents = max($totalCents - $paidCents, 0);

            return [
                'sale' => $sale,
                'totalCents' => $totalCents,
                'paidCents' => $paidCents,
                'balanceCents' => $balanceCents,
            ];
        })->filter(fn (array $row) => $row['balanceCents'] > 0)->values();

        $summary = [
            'count' => $rows->count(),
            'balanceCents' => $rows->sum(fn (array $row) => $row['balanceCents']),
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('admin.reports.pending-payments', [
            'rows' => $rows,
            'summary' => $summary,
            'range' => $range,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-pagos-pendientes-' . $range['key'] . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    /**
     * @return array{key: string, label: string, start: Carbon, end: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $customStart = $this->tryParseDate($request->query('start_date'));
        $customEnd = $this->tryParseDate($request->query('end_date'));

        if ($customStart || $customEnd) {
            $start = ($customStart ?? $customEnd ?? now())->copy()->startOfDay();
            $end = ($customEnd ?? $customStart ?? now())->copy()->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [
                'key' => 'rango-' . $start->format('Y-m-d') . '-a-' . $end->format('Y-m-d'),
                'label' => 'Desde/Hasta',
                'start' => $start,
                'end' => $end,
            ];
        }

        $key = (string) $request->query('range', 'mes');
        $now = now();

        return match ($key) {
            'hoy' => [
                'key' => 'hoy',
                'label' => 'Hoy',
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'semana' => [
                'key' => 'semana',
                'label' => 'Semana',
                'start' => $now->copy()->startOfWeek()->startOfDay(),
                'end' => $now->copy()->endOfWeek()->endOfDay(),
            ],
            default => [
                'key' => 'mes',
                'label' => 'Mes',
                'start' => $now->copy()->startOfMonth()->startOfDay(),
                'end' => $now->copy()->endOfMonth()->endOfDay(),
            ],
        };
    }

    private function tryParseDate(mixed $value): ?Carbon
    {
        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        try {
            return Carbon::parse($stringValue);
        } catch (Throwable) {
            return null;
        }
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
