<?php

use App\Models\Sale;
use Illuminate\Database\QueryException;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    /**
     * @var array<int, array{id:int, customer_name:string, created_at:string, total_amount:string}>
     */
    public array $results = [];

    public ?string $error = null;

    public function mount(): void
    {
        $initialQuery = trim((string) request()->query('sale_note_query', ''));

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
            $sales = Sale::query()
                ->select(['id', 'customer_name', 'total_amount', 'created_at'])
                ->where('customer_name', 'like', '%' . $term . '%')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            $this->results = $sales
                ->map(fn (Sale $sale) => [
                    'id' => $sale->id,
                    'customer_name' => $sale->customer_name,
                    'created_at' => $sale->created_at?->format('d/m/Y H:i') ?? '—',
                    'total_amount' => (string) $sale->total_amount,
                ])
                ->all();
        } catch (QueryException) {
            $this->error = 'No se pudo buscar ventas. Revise la conexión a la base de datos y ejecute las migraciones si es un servidor nuevo.';
        }
    }
};
?>

<div>
    <div class="mt-5 space-y-4">
        <div>
            <label for="sale_note_query" class="mb-2 block text-lg font-bold text-foreground">
                Buscar cliente
            </label>
            <input type="text" id="sale_note_query" placeholder="Ejemplo: Juan" wire:model.live.debounce.350ms="query"
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
                    @forelse ($results as $sale)
                        <div class="rounded-2xl border border-border bg-surface p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-lg font-extrabold text-foreground">Venta #{{ $sale['id'] }}</div>
                                    <div class="mt-1 text-lg text-foreground-muted sm:text-xl">
                                        {{ $sale['customer_name'] }} · {{ $sale['created_at'] }} · Total {{ $sale['total_amount'] }}
                                    </div>
                                </div>

                                <a href="{{ route('sales.note', $sale['id']) }}"
                                    class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-primary px-5 py-3 text-lg font-bold text-primary-foreground transition hover:bg-primary-hover">
                                    <span class="text-2xl">⬇️</span>
                                    <span>Nota (PDF)</span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-border bg-surface p-4 text-lg text-foreground-muted shadow-sm">
                            No se encontraron ventas para “{{ $trimmedQuery }}”.
                        </div>
                    @endforelse
                </div>
            @endif
        @endif
    </div>
</div>
