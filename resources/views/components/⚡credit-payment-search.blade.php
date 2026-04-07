<?php

use App\Models\Sale;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $query = '';

    public ?string $error_message = null;

    public function mount(): void
    {
        $this->query = trim((string) request()->query('customer_query', ''));
    }

    public function updatedQuery(): void
    {
        $this->resetPage('credits_page');
    }

    private function pendingSalesPaginator(): LengthAwarePaginator
    {
        $this->error_message = null;

        try {
            return Sale::query()
                ->select(['id', 'customer_name', 'delivery_location', 'due_date', 'total_amount', 'created_at'])
                ->with([
                    'items' => fn ($query) => $query->select(['id', 'sale_id', 'product_id', 'quantity'])->with('product:id,name'),
                ])
                ->withSum('payments', 'amount')
                ->whereRaw('CAST(total_amount AS DECIMAL(15,2)) > COALESCE((SELECT SUM(amount) FROM sale_payments WHERE sale_payments.sale_id = sales.id), 0)')
                ->when(trim($this->query) !== '', function ($query) {
                    $query->where('customer_name', 'like', '%' . trim($this->query) . '%');
                })
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'credits_page')
                ->withQueryString();
        } catch (QueryException) {
            $this->error_message = 'No se pudieron cargar los créditos. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.';

            return new LengthAwarePaginator(
                [],
                0,
                10,
                1,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => 'credits_page',
                ]
            );
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

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
};
?>

<div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
    @php
        $sales = $this->pendingSalesPaginator();
    @endphp

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Buscar cliente</h2>
            <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                Escriba el nombre del cliente para registrar su pago pendiente.
            </p>
        </div>

        <a href="{{ route('home') }}"
            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-muted px-5 py-3 text-lg font-bold text-foreground transition hover:bg-border">
            <span class="text-2xl">↩️</span>
            <span>Inicio</span>
        </a>
    </div>

    <div class="mt-5">
        <label for="customer_query" class="mb-2 block text-lg font-bold text-foreground">
            Cliente
        </label>
        <input
            id="customer_query"
            type="text"
            wire:model.live.debounce.350ms="query"
            placeholder="Ejemplo: Juan"
            class="min-h-[60px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-warning focus:ring-4 focus:ring-warning/20 sm:text-xl"
        >

        <div wire:loading.flex wire:target="query" class="mt-2 items-center gap-2 text-sm font-semibold text-foreground-muted">
            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-border border-t-warning"></span>
            <span>Buscando cliente...</span>
        </div>
    </div>

    @if ($error_message)
        <div class="mt-5 rounded-2xl border border-danger-border bg-danger-soft p-4 text-lg text-danger sm:text-xl">
            ⚠️ {{ $error_message }}
        </div>
    @else
        <div class="mt-6 space-y-4">
            @forelse ($sales as $sale)
                <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm">
                    @php
                        $totalCents = $this->moneyToCents($sale->total_amount);
                        $paidCents = $this->moneyToCents($sale->payments_sum_amount ?? 0);
                        $balanceCents = max($totalCents - $paidCents, 0);
                    @endphp

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Venta</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">#{{ $sale->id }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">👤 Cliente</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->customer_name }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha de venta</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📍 Lugar de entrega</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->delivery_location ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha prometida</span>
                            <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Total</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $this->formatCents($totalCents) }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Pagado</span>
                            <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $this->formatCents($paidCents) }}</span>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Saldo</span>
                            <span class="text-2xl font-extrabold text-foreground">{{ $this->formatCents($balanceCents) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-border bg-surface p-4">
                        <span class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧺 Productos del día</span>

                        @if ($sale->items->count() > 0)
                            <div class="space-y-1">
                                @foreach ($sale->items as $item)
                                    <div class="text-lg text-foreground sm:text-xl">• {{ $item->quantity }} x {{ $item->product?->name ?? 'Producto no disponible' }}</div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-lg text-foreground-muted sm:text-xl">Sin productos registrados.</div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('sales.show', ['sale' => $sale->id, 'payment_only' => 1]) }}"
                            class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition hover:bg-warning-hover sm:text-xl">
                            <span class="text-2xl">✅</span>
                            <span>Registrar pago</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-border bg-muted p-4 text-lg text-foreground-muted shadow-sm">
                    @if (trim($query) === '')
                        No hay créditos/pagos pendientes.
                    @else
                        No se encontraron clientes con saldo pendiente para “{{ trim($query) }}”.
                    @endif
                </div>
            @endforelse
        </div>

        @if ($sales->hasPages())
            <div class="mt-6 overflow-x-auto">
                {{ $sales->links() }}
            </div>
        @endif
    @endif
</div>
