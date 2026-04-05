<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas text-foreground">
    <div class="mx-auto max-w-7xl p-3 sm:p-4">
        <div class="rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-soft text-3xl">
                        📊
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold leading-tight text-foreground sm:text-4xl">
                            Panel del administrador
                        </h1>
                        <p class="mt-2 text-lg text-foreground-muted sm:text-xl">
                            Control de salidas registradas por los empleados.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="w-full lg:w-auto" novalidate>
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-3 rounded-2xl bg-danger px-5 py-3 text-lg font-bold text-danger-foreground transition hover:bg-danger-hover lg:w-auto"
                    >
                        <span class="text-2xl">🚪</span>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-2xl border border-success-border bg-success-soft px-4 py-4 text-lg font-medium text-success sm:text-xl">
                ✅ {{ session('status') }}
            </div>
        @endif

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-surface p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <p class="text-base font-semibold text-foreground-muted">Total de registros</p>
                        <p class="mt-1 text-3xl font-extrabold text-foreground sm:text-4xl">{{ $totalRecords }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-surface p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📦</span>
                    <div>
                        <p class="text-base font-semibold text-foreground-muted">Unidades retiradas</p>
                        <p class="mt-1 text-3xl font-extrabold text-foreground sm:text-4xl">{{ $totalUnits }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-surface p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📅</span>
                    <div>
                        <p class="text-base font-semibold text-foreground-muted">Registros de hoy</p>
                        <p class="mt-1 text-3xl font-extrabold text-foreground sm:text-4xl">{{ $todayRecords }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-surface p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⏱️</span>
                    <div>
                        <p class="text-base font-semibold text-foreground-muted">Unidades hoy</p>
                        <p class="mt-1 text-3xl font-extrabold text-foreground sm:text-4xl">{{ $todayUnits }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🔎</span>
                <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                    Filtros
                </h2>
            </div>

            <p class="mt-2 text-lg text-foreground-muted sm:text-xl">
                Use estos filtros para buscar registros específicos.
            </p>

            <form method="GET" action="{{ route('dashboard') }}" class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-5" novalidate>
                <div>
                    <label for="product_id" class="mb-2 flex items-center gap-2 text-lg font-bold text-foreground">
                        <span>🧴</span>
                        <span>Producto</span>
                    </label>
                    <select
                        name="product_id"
                        id="product_id"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20"
                    >
                        <option value="">Todos</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="start_date" class="mb-2 flex items-center gap-2 text-lg font-bold text-foreground">
                        <span>📆</span>
                        <span>Desde</span>
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ request('start_date') }}"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20"
                    >
                </div>

                <div>
                    <label for="end_date" class="mb-2 flex items-center gap-2 text-lg font-bold text-foreground">
                        <span>📆</span>
                        <span>Hasta</span>
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        value="{{ request('end_date') }}"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-lg font-bold text-primary-foreground transition hover:bg-primary-hover"
                    >
                        <span>🔍</span>
                        <span>Filtrar</span>
                    </button>
                </div>

                <div class="flex items-end">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-2 rounded-2xl bg-muted px-4 py-3 text-lg font-bold text-foreground transition hover:bg-border"
                    >
                        <span>🧹</span>
                        <span>Limpiar</span>
                    </a>
                </div>
            </form>
        </div>

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

            <div class="mt-5 hidden overflow-x-auto xl:block">
                <table class="min-w-[1050px] w-full border-collapse overflow-hidden rounded-2xl">
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
                            <tr class="border-b border-border align-top">
                                <td class="px-4 py-4">{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4">{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                                <td class="px-4 py-4">{{ $output->quantity }}</td>
                                <td class="px-4 py-4">{{ $output->employee_name ?: '—' }}</td>
                                <td class="px-4 py-4">{{ $output->notes ?: '—' }}</td>
                                <td class="px-4 py-4">
                                    <button
                                        type="button"
                                        class="open-delete-modal inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-danger px-4 py-3 text-base font-bold text-danger-foreground transition hover:bg-danger-hover"
                                        data-action="{{ route('admin.stock-outputs.destroy', $output) }}"
                                        data-product="{{ $output->product?->name ?? 'Producto no disponible' }}"
                                        data-quantity="{{ $output->quantity }}"
                                        data-date="{{ $output->moved_at?->format('d/m/Y H:i') }}"
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
                    <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm sm:p-5">
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
                                    class="open-delete-modal inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-danger px-4 py-3 text-xl font-bold text-danger-foreground transition hover:bg-danger-hover"
                                    data-action="{{ route('admin.stock-outputs.destroy', $output) }}"
                                    data-product="{{ $output->product?->name ?? 'Producto no disponible' }}"
                                    data-quantity="{{ $output->quantity }}"
                                    data-date="{{ $output->moved_at?->format('d/m/Y H:i') }}"
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
        </div>

        @php
            $formatCents = fn(int $cents) => number_format($cents / 100, 2, '.', '');
        @endphp

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

            <div class="mt-5 hidden overflow-x-auto xl:block">
                <table class="min-w-[1250px] w-full border-collapse overflow-hidden rounded-2xl">
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
                            <tr class="border-b border-border align-top">
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
                                        class="open-delete-sale-modal inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-danger px-4 py-3 text-base font-bold text-danger-foreground transition hover:bg-danger-hover"
                                        data-action="{{ route('admin.sales.destroy', $sale) }}"
                                        data-sale="#{{ $sale->id }}"
                                        data-customer="{{ $sale->customer_name }}"
                                        data-total="{{ $formatCents($row['totalCents']) }}"
                                        data-date="{{ $sale->created_at?->format('d/m/Y H:i') }}"
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
                    <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm sm:p-5">
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
                                    class="open-delete-sale-modal inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-danger px-4 py-3 text-xl font-bold text-danger-foreground transition hover:bg-danger-hover"
                                    data-action="{{ route('admin.sales.destroy', $sale) }}"
                                    data-sale="#{{ $sale->id }}"
                                    data-customer="{{ $sale->customer_name }}"
                                    data-total="{{ $formatCents($row['totalCents']) }}"
                                    data-date="{{ $sale->created_at?->format('d/m/Y H:i') }}"
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
        </div>
    </div>

    <x-delete-modal
        modal-id="deleteModal"
        cancel-id="cancelDelete"
        confirm-id="confirmDelete"
        title="Eliminar registro"
        subtitle="Revise la información antes de confirmar."
    />

    <x-delete-modal
        modal-id="deleteSaleModal"
        form-id="deleteSaleModalForm"
        cancel-id="cancelDeleteSale"
        confirm-id="confirmDeleteSale"
        title="Eliminar venta"
        subtitle="Revise la información antes de confirmar."
        first-label="Venta"
        first-id="deleteSalePreviewSale"
        second-label="Total"
        second-id="deleteSalePreviewTotal"
        third-label="Fecha"
        third-id="deleteSalePreviewDate"
    />

    <script>
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalForm = document.getElementById('deleteModalForm');
        const cancelDelete = document.getElementById('cancelDelete');

        const deletePreviewProduct = document.getElementById('deletePreviewProduct');
        const deletePreviewQuantity = document.getElementById('deletePreviewQuantity');
        const deletePreviewDate = document.getElementById('deletePreviewDate');

        document.querySelectorAll('.open-delete-modal').forEach(button => {
            button.addEventListener('click', () => {
                if (!deleteModal || !deleteModalForm || !cancelDelete) {
                    console.error('No se encontró correctamente el modal de eliminación.');
                    return;
                }

                deleteModalForm.action = button.dataset.action;
                deletePreviewProduct.textContent = button.dataset.product || '—';
                deletePreviewQuantity.textContent = button.dataset.quantity || '—';
                deletePreviewDate.textContent = button.dataset.date || '—';

                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
            });
        });

        cancelDelete.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        });

        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
            }
        });

        const deleteSaleModal = document.getElementById('deleteSaleModal');
        const deleteSaleModalForm = document.getElementById('deleteSaleModalForm');
        const cancelDeleteSale = document.getElementById('cancelDeleteSale');

        const deleteSalePreviewSale = document.getElementById('deleteSalePreviewSale');
        const deleteSalePreviewTotal = document.getElementById('deleteSalePreviewTotal');
        const deleteSalePreviewDate = document.getElementById('deleteSalePreviewDate');

        document.querySelectorAll('.open-delete-sale-modal').forEach(button => {
            button.addEventListener('click', () => {
                if (!deleteSaleModal || !deleteSaleModalForm || !cancelDeleteSale) {
                    console.error('No se encontró correctamente el modal de eliminación de ventas.');
                    return;
                }

                const sale = button.dataset.sale || '—';
                const customer = button.dataset.customer || '—';

                deleteSaleModalForm.action = button.dataset.action;
                deleteSalePreviewSale.textContent = `${sale} — ${customer}`;
                deleteSalePreviewTotal.textContent = button.dataset.total || '—';
                deleteSalePreviewDate.textContent = button.dataset.date || '—';

                deleteSaleModal.classList.remove('hidden');
                deleteSaleModal.classList.add('flex');
            });
        });

        cancelDeleteSale?.addEventListener('click', () => {
            deleteSaleModal?.classList.add('hidden');
            deleteSaleModal?.classList.remove('flex');
        });

        deleteSaleModal?.addEventListener('click', (e) => {
            if (e.target === deleteSaleModal) {
                deleteSaleModal.classList.add('hidden');
                deleteSaleModal.classList.remove('flex');
            }
        });
    </script>
</body>

</html>