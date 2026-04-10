<?php

use App\Models\SalePayment;
use Illuminate\Database\QueryException;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    /**
     * @var array<int, array{sale_id:int, payment_id:int, customer_name:string, paid_at:string, amount:string}>
     */
    public array $results = [];

    public ?string $error = null;

    public function mount(): void
    {
        $initialQuery = trim((string) request()->query('payment_receipt_query', ''));

        if ($initialQuery !== '') {
            $this->query = $initialQuery;
            $this->search();
        }
    }

    public function updatedQuery(): void
    {
        $this->search();
    }

    public function search(): void
    {
        $term = trim($this->query);

        $this->error = null;
        $this->results = [];

        if ($term === '') {
            return;
        }

        try {
            $payments = SalePayment::query()
                ->select(['id', 'sale_id', 'amount', 'paid_at'])
                ->with(['sale:id,customer_name'])
                ->whereHas('sale', fn ($query) => $query->where('customer_name', 'like', '%' . $term . '%'))
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get();

            $this->results = $payments
                ->map(fn (SalePayment $payment) => [
                    'sale_id' => (int) $payment->sale_id,
                    'payment_id' => (int) $payment->id,
                    'customer_name' => (string) ($payment->sale?->customer_name ?? 'Cliente'),
                    'paid_at' => $payment->paid_at?->format('d/m/Y H:i') ?? '—',
                    'amount' => (string) $payment->amount,
                ])
                ->all();
        } catch (QueryException) {
            $this->error = 'No se pudo buscar pagos. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.';
        }
    }
};
?>

<div>
    <div class="mt-5 space-y-4">
        <div>
            <label for="payment_receipt_query" class="mb-2 block text-lg font-bold text-foreground">
                Buscar cliente
            </label>
            <input type="text" id="payment_receipt_query" placeholder="Ejemplo: Juan" wire:model.live.debounce.350ms="query"
                class="min-h-[60px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20 sm:text-xl">
        </div>

        @if ($error)
            <div class="rounded-2xl border border-danger-border bg-danger-soft p-4 text-lg text-danger sm:text-xl">
                ⚠️ {{ $error }}
            </div>
        @else
            @php
                $trimmedQuery = trim($query);
            @endphp

            @if ($trimmedQuery !== '')
                <div class="space-y-3">
                    @forelse ($results as $row)
                        <div class="rounded-2xl border border-border bg-surface p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-lg font-extrabold text-foreground">
                                        Venta #{{ $row['sale_id'] }} · Pago #{{ $row['payment_id'] }}
                                    </div>
                                    <div class="mt-1 text-lg text-foreground-muted sm:text-xl">
                                        {{ $row['customer_name'] }} · {{ $row['paid_at'] }} · Monto {{ $row['amount'] }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <a href="{{ route('sales.payments.receipt', ['sale' => $row['sale_id'], 'payment' => $row['payment_id']]) }}"
                                        class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition hover:bg-warning-hover">
                                        <span class="text-2xl">📄</span>
                                        <span>Recibo (PDF)</span>
                                    </a>

                                    <a href="{{ route('sales.payments.receipt.image', ['sale' => $row['sale_id'], 'payment' => $row['payment_id']]) }}"
                                        class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-primary px-5 py-3 text-lg font-bold text-primary-foreground transition hover:bg-primary-hover">
                                        <span class="text-2xl">🖼️</span>
                                        <span>Recibo (Imagen)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-border bg-surface p-4 text-lg text-foreground-muted shadow-sm">
                            No se encontraron pagos para “{{ $trimmedQuery }}”.
                        </div>
                    @endforelse
                </div>
            @endif
        @endif
    </div>
</div>
