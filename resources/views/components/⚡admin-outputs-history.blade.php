<?php

use App\Models\StockOutput;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?int $product_id = null;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public bool $confirming_delete = false;
    public ?int $delete_id = null;

    public string $delete_product = '—';
    public string $delete_quantity = '—';
    public string $delete_date = '—';

    public ?string $status_message = null;
    public ?string $error_message = null;

    public function mount($productId = null, $startDate = null, $endDate = null): void
    {
        $this->product_id = is_numeric($productId) ? (int) $productId : null;

        $startDate = is_string($startDate) ? trim($startDate) : '';
        $endDate = is_string($endDate) ? trim($endDate) : '';

        $this->start_date = $startDate !== '' ? $startDate : null;
        $this->end_date = $endDate !== '' ? $endDate : null;
    }

    public function confirmDelete(int $id): void
    {
        $this->status_message = null;
        $this->error_message = null;

        $output = StockOutput::with('product')->find($id);

        if (! $output) {
            $this->error_message = 'No se encontró el registro.';
            return;
        }

        $this->delete_id = $output->id;
        $this->delete_product = $output->product?->name ?? 'Producto no disponible';
        $this->delete_quantity = (string) $output->quantity;
        $this->delete_date = $output->moved_at?->format('d/m/Y H:i') ?? '—';

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
            $deleted = StockOutput::whereKey($this->delete_id)->delete();

            if ($deleted < 1) {
                $this->error_message = 'No se pudo eliminar el registro.';
            } else {
                $this->status_message = 'Registro eliminado correctamente.';
            }
        } catch (QueryException) {
            $this->error_message = 'No se pudo eliminar el registro. Revise la conexión a la base de datos.';
        }

        $this->confirming_delete = false;
        $this->delete_id = null;

        if ($this->getPage() > 1) {
            $outputs = $this->outputsQuery()->paginate(15);

            if ($outputs->isEmpty()) {
                $this->previousPage();
            }
        }
    }

    private function outputsQuery()
    {
        return StockOutput::with('product')
            ->when($this->product_id, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($this->start_date, function ($query, $startDate) {
                $query->whereDate('moved_at', '>=', $startDate);
            })
            ->when($this->end_date, function ($query, $endDate) {
                $query->whereDate('moved_at', '<=', $endDate);
            })
            ->orderByDesc('moved_at')
            ->orderByDesc('id');
    }
};
?>

<div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
    <div class="flex items-center gap-3">
        <span class="text-3xl">🗂️</span>
        <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
            Historial de salidas
        </h2>
    </div>

    <p class="mt-2 text-lg text-foreground-muted sm:text-xl">
        Revise los registros guardados y elimine si es necesario.
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
        $outputs = $this->outputsQuery()
            ->paginate(15)
            ->withQueryString();
    @endphp

    <div class="mt-5 hidden overflow-x-auto xl:block">
        <table class="w-full min-w-[1050px] border-collapse overflow-hidden rounded-2xl">
            <thead>
                <tr class="bg-muted text-left text-base font-bold text-foreground">
                    <th class="px-4 py-4">📅 Fecha</th>
                    <th class="px-4 py-4">🧴 Producto</th>
                    <th class="px-4 py-4">🔢 Cantidad</th>
                    <th class="px-4 py-4">👤 Empleado</th>
                    <th class="px-4 py-4">📝 Observación</th>
                    <th class="px-4 py-4">🗑️ Acción</th>
                </tr>
            </thead>
            <tbody class="bg-surface text-base text-foreground">
                @forelse($outputs as $output)
                    <tr class="border-b border-border align-top" wire:key="output-row-{{ $output->id }}">
                        <td class="px-4 py-4">{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-4">{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                        <td class="px-4 py-4">{{ $output->quantity }}</td>
                        <td class="px-4 py-4">{{ $output->employee_name ?: '—' }}</td>
                        <td class="px-4 py-4">{{ $output->notes ?: '—' }}</td>
                        <td class="px-4 py-4">
                            <button
                                type="button"
                                wire:click="confirmDelete({{ $output->id }})"
                                class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-danger px-4 py-3 text-base font-bold text-danger-foreground transition hover:bg-danger-hover"
                            >
                                <span>🗑️</span>
                                <span>Eliminar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-lg text-foreground-muted">
                            No hay registros para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 space-y-4 xl:hidden">
        @forelse($outputs as $output)
            <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm sm:p-5" wire:key="output-card-{{ $output->id }}">
                <div class="space-y-4">
                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $output->moved_at?->format('d/m/Y H:i') }}</span>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧴 Producto</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $output->product?->name ?? 'Producto no disponible' }}</span>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🔢 Cantidad</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $output->quantity }}</span>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">👤 Empleado</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $output->employee_name ?: '—' }}</span>
                    </div>

                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📝 Observación</span>
                        <span class="text-lg font-medium text-foreground sm:text-xl">{{ $output->notes ?: '—' }}</span>
                    </div>

                    <div class="pt-1">
                        <button
                            type="button"
                            wire:click="confirmDelete({{ $output->id }})"
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
                No hay registros para mostrar en este momento.
            </div>
        @endforelse
    </div>

    <div class="mt-6 overflow-x-auto">
        {{ $outputs->links() }}
    </div>

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
                            Eliminar registro
                        </h3>
                        <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                            Revise la información antes de confirmar.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-danger-border bg-danger-soft p-4">
                    <p class="text-lg text-foreground sm:text-xl">
                        ¿Está seguro que desea eliminar este registro?
                    </p>

                    <div class="mt-4 space-y-3 rounded-2xl border border-border bg-surface p-4">
                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">Producto</span>
                            <p class="text-xl font-semibold text-foreground sm:text-2xl">{{ $delete_product }}</p>
                        </div>

                        <div>
                            <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">Cantidad</span>
                            <p class="text-xl font-semibold text-foreground sm:text-2xl">{{ $delete_quantity }}</p>
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
