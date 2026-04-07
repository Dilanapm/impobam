<?php

use App\Models\Sale;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public bool $confirming_delete = false;
    public ?int $delete_id = null;

    public string $delete_sale = '—';
    public string $delete_total = '—';
    public string $delete_date = '—';

    public ?string $status_message = null;
    public ?string $error_message = null;

    public function confirmDelete(int $id): void
    {
        $this->status_message = null;
        $this->error_message = null;

        $sale = Sale::query()->select(['id', 'customer_name', 'total_amount', 'created_at'])->find($id);

        if (! $sale) {
            $this->error_message = 'No se encontró la venta.';
            return;
        }

        $this->delete_id = $sale->id;
        $this->delete_sale = "#{$sale->id} — {$sale->customer_name}";

        $totalCents = $this->moneyToCents($sale->total_amount);
        $this->delete_total = $this->formatCents($totalCents);
        $this->delete_date = $sale->created_at?->format('d/m/Y H:i') ?? '—';

        $this->confirming_delete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirming_delete = false;
        $this->delete_id = null;
    }

    public function deleteConfirmed(): void
    {
        $this->status_message = null;
        $this->error_message = null;

        if (! $this->delete_id) {
            $this->confirming_delete = false;
            return;
        }

        try {
            $deleted = Sale::whereKey($this->delete_id)->delete();

            if ($deleted < 1) {
                $this->error_message = 'No se pudo eliminar la venta.';
            } else {
                $this->status_message = 'Venta eliminada correctamente.';
            }
        } catch (QueryException) {
            $this->error_message = 'No se pudo eliminar la venta. Revise la conexión a la base de datos.';
        }

        $this->confirming_delete = false;
        $this->delete_id = null;

        if ($this->getPage('sales_page') > 1) {
            $sales = $this->salesPaginator();

            if ($sales->isEmpty()) {
                $this->previousPage('sales_page');
            }
        }
    }

    private function salesPaginator(): LengthAwarePaginator
    {
        return Sale::query()
            ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
            ->withSum('payments', 'amount')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'sales_page')
            ->withQueryString();
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

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
};
?>

<div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
    <div class="flex items-center gap-3">
        <span class="text-3xl">🧾</span>
        <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
            Historial de ventas
        </h2>
    </div>

    <p class="mt-2 text-lg text-foreground-muted sm:text-xl">
        Revise las ventas registradas y elimine si es necesario.
    </p>

    @if ($status_message)
        <div class="mt-5 rounded-2xl border border-success-border bg-success-soft px-4 py-4 text-lg font-medium text-success sm:text-xl">
            ✅ {{ $status_message }}
        </div>
    @endif

    @if ($error_message)
        <div class="mt-5 rounded-2xl border border-danger-border bg-danger-soft px-4 py-4 text-lg font-medium text-danger sm:text-xl">
            ⚠️ {{ $error_message }}
        </div>
    @endif

    @php
        $salesLoadError = null;

        try {
            $sales = $this->salesPaginator();

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
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => 'sales_page',
                ]
            );
        }

        $formatCents = fn (int $cents) => $this->formatCents($cents);
    @endphp

    <div class="mt-5 hidden overflow-x-auto xl:block">
        <table class="w-full min-w-[1250px] border-collapse overflow-hidden rounded-2xl">
            <thead>
                <tr class="bg-muted text-left text-base font-bold text-foreground">
                    <th class="px-4 py-4">📅 Fecha</th>
                    <th class="px-4 py-4">🧾 Venta</th>
                    <th class="px-4 py-4">👤 Cliente</th>
                    <th class="px-4 py-4">🧾 Total</th>
                    <th class="px-4 py-4">💳 Pagado</th>
                    <th class="px-4 py-4">⏳ Saldo</th>
                    <th class="px-4 py-4">📅 Promesa</th>
                    <th class="px-4 py-4">🗑️ Acción</th>
                </tr>
            </thead>
            <tbody class="bg-surface text-base text-foreground">
                @forelse($sales as $row)
                    @php
                        /** @var \App\Models\Sale $sale */
                        $sale = $row['sale'];
                    @endphp
                    <tr class="border-b border-border align-top" wire:key="sale-row-{{ $sale->id }}">
                        <td class="px-4 py-4">{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-4">#{{ $sale->id }}</td>
                        <td class="px-4 py-4">{{ $sale->customer_name }}</td>
                        <td class="px-4 py-4">{{ $formatCents($row['totalCents']) }}</td>
                        <td class="px-4 py-4">{{ $formatCents($row['paidCents']) }}</td>
                        <td class="px-4 py-4">{{ $formatCents($row['balanceCents']) }}</td>
                        <td class="px-4 py-4">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-4">
                            <button
                                type="button"
                                wire:click="confirmDelete({{ $sale->id }})"
                                class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-danger px-4 py-3 text-base font-bold text-danger-foreground transition hover:bg-danger-hover"
                            >
                                <span>🗑️</span>
                                <span>Eliminar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-lg text-foreground-muted">
                            {{ $salesLoadError ?? 'No hay ventas para mostrar.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 space-y-4 xl:hidden">
        @forelse($sales as $row)
            @php
                /** @var \App\Models\Sale $sale */
                $sale = $row['sale'];
            @endphp
            <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm sm:p-5" wire:key="sale-card-{{ $sale->id }}">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->created_at?->format('d/m/Y H:i') }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Venta</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">#{{ $sale->id }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">👤 Cliente</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->customer_name }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Total</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $formatCents($row['totalCents']) }}</span>
                        </div>
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Pagado</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $formatCents($row['paidCents']) }}</span>
                        </div>
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Saldo</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $formatCents($row['balanceCents']) }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Promesa</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>

                    <div class="pt-1">
                        <button
                            type="button"
                            wire:click="confirmDelete({{ $sale->id }})"
                            class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-danger px-4 py-3 text-xl font-bold text-danger-foreground transition hover:bg-danger-hover"
                        >
                            <span class="text-2xl">🗑️</span>
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-border bg-muted p-4 text-lg text-foreground-muted shadow-sm">
                {{ $salesLoadError ?? 'No hay ventas para mostrar en este momento.' }}
            </div>
        @endforelse
    </div>

    @if (empty($salesLoadError))
        <div class="mt-6 overflow-x-auto">
            {{ $sales->links() }}
        </div>
    @endif

    @if ($confirming_delete)
        <div
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-3 sm:items-center"
            wire:click.self="cancelDelete"
        >
            <div class="w-full max-w-2xl rounded-3xl bg-surface p-5 shadow-2xl sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🗑️</span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                            Eliminar venta
                        </h3>
                        <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                            Revise la información antes de confirmar.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-danger-border bg-danger-soft p-4">
                    <p class="text-lg text-foreground sm:text-xl">
                        ¿Está seguro que desea eliminar esta venta?
                    </p>

                    <div class="mt-4 space-y-3 rounded-2xl border border-border bg-surface p-4">
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">Venta</span>
                            <p class="text-xl font-semibold text-foreground sm:text-2xl">{{ $delete_sale }}</p>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">Total</span>
                            <p class="text-xl font-semibold text-foreground sm:text-2xl">{{ $delete_total }}</p>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">Fecha</span>
                            <p class="text-xl font-semibold text-foreground sm:text-2xl">{{ $delete_date }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-muted px-4 py-3 text-xl font-bold text-foreground transition hover:bg-border"
                    >
                        <span class="text-2xl">↩️</span>
                        <span>Cancelar</span>
                    </button>

                    <button
                        type="button"
                        wire:click="deleteConfirmed"
                        class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-danger px-4 py-3 text-xl font-bold text-danger-foreground transition hover:bg-danger-hover"
                    >
                        <span class="text-2xl">🗑️</span>
                        <span>Eliminar</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
