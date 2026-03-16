<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-7xl p-3 sm:p-4">
        <div class="rounded-3xl bg-white p-4 shadow-lg sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-3xl">
                        📊
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold leading-tight text-slate-800 sm:text-4xl">
                            Panel del administrador
                        </h1>
                        <p class="mt-2 text-lg text-slate-500 sm:text-xl">
                            Control de salidas registradas por los empleados.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="w-full lg:w-auto">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-3 rounded-2xl bg-red-600 px-5 py-3 text-lg font-bold text-white transition hover:bg-red-700 lg:w-auto"
                    >
                        <span class="text-2xl">🚪</span>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-lg font-medium text-emerald-800 sm:text-xl">
                ✅ {{ session('status') }}
            </div>
        @endif

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <p class="text-base font-semibold text-slate-500">Total de registros</p>
                        <p class="mt-1 text-3xl font-extrabold text-slate-800 sm:text-4xl">{{ $totalRecords }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📦</span>
                    <div>
                        <p class="text-base font-semibold text-slate-500">Unidades retiradas</p>
                        <p class="mt-1 text-3xl font-extrabold text-slate-800 sm:text-4xl">{{ $totalUnits }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📅</span>
                    <div>
                        <p class="text-base font-semibold text-slate-500">Registros de hoy</p>
                        <p class="mt-1 text-3xl font-extrabold text-slate-800 sm:text-4xl">{{ $todayRecords }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⏱️</span>
                    <div>
                        <p class="text-base font-semibold text-slate-500">Unidades hoy</p>
                        <p class="mt-1 text-3xl font-extrabold text-slate-800 sm:text-4xl">{{ $todayUnits }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-3xl bg-white p-4 shadow-lg sm:p-6">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🔎</span>
                <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                    Filtros
                </h2>
            </div>

            <p class="mt-2 text-lg text-slate-500 sm:text-xl">
                Use estos filtros para buscar registros específicos.
            </p>

            <form method="GET" action="{{ route('dashboard') }}" class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div>
                    <label for="product_id" class="mb-2 flex items-center gap-2 text-lg font-bold text-slate-700">
                        <span>🧴</span>
                        <span>Producto</span>
                    </label>
                    <select
                        name="product_id"
                        id="product_id"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200"
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
                    <label for="start_date" class="mb-2 flex items-center gap-2 text-lg font-bold text-slate-700">
                        <span>📆</span>
                        <span>Desde</span>
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ request('start_date') }}"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200"
                    >
                </div>

                <div>
                    <label for="end_date" class="mb-2 flex items-center gap-2 text-lg font-bold text-slate-700">
                        <span>📆</span>
                        <span>Hasta</span>
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        value="{{ request('end_date') }}"
                        class="min-h-[56px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-lg font-bold text-white transition hover:bg-blue-700"
                    >
                        <span>🔍</span>
                        <span>Filtrar</span>
                    </button>
                </div>

                <div class="flex items-end">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex min-h-[56px] w-full items-center justify-center gap-2 rounded-2xl bg-slate-100 px-4 py-3 text-lg font-bold text-slate-700 transition hover:bg-slate-200"
                    >
                        <span>🧹</span>
                        <span>Limpiar</span>
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-5 rounded-3xl bg-white p-4 shadow-lg sm:p-6">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🗂️</span>
                <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                    Historial de salidas
                </h2>
            </div>

            <p class="mt-2 text-lg text-slate-500 sm:text-xl">
                Revise los registros guardados y elimine si es necesario.
            </p>

            <div class="mt-5 hidden overflow-x-auto xl:block">
                <table class="min-w-[1050px] w-full border-collapse overflow-hidden rounded-2xl">
                    <thead>
                        <tr class="bg-slate-100 text-left text-base font-bold text-slate-700">
                            <th class="px-4 py-4">📅 Fecha</th>
                            <th class="px-4 py-4">🧴 Producto</th>
                            <th class="px-4 py-4">🔢 Cantidad</th>
                            <th class="px-4 py-4">👤 Empleado</th>
                            <th class="px-4 py-4">📝 Observación</th>
                            <th class="px-4 py-4">🗑️ Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-base text-slate-700">
                        @forelse($outputs as $output)
                            <tr class="border-b border-slate-200 align-top">
                                <td class="px-4 py-4">{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4">{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                                <td class="px-4 py-4">{{ $output->quantity }}</td>
                                <td class="px-4 py-4">{{ $output->employee_name ?: '—' }}</td>
                                <td class="px-4 py-4">{{ $output->notes ?: '—' }}</td>
                                <td class="px-4 py-4">
                                    <button
                                        type="button"
                                        class="open-delete-modal inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-base font-bold text-white transition hover:bg-red-700"
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
                                <td colspan="6" class="px-4 py-6 text-center text-lg text-slate-500">
                                    No hay registros para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5 space-y-4 xl:hidden">
                @forelse($outputs as $output)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5">
                        <div class="space-y-4">
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📅 Fecha</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->moved_at?->format('d/m/Y H:i') }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🧴 Producto</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->product?->name ?? 'Producto no disponible' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🔢 Cantidad</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->quantity }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">👤 Empleado</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->employee_name ?: '—' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📝 Observación</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->notes ?: '—' }}</span>
                            </div>

                            <div class="pt-1">
                                <button
                                    type="button"
                                    class="open-delete-modal inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-red-600 px-4 py-3 text-xl font-bold text-white transition hover:bg-red-700"
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
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-lg text-slate-500 shadow-sm">
                        No hay registros para mostrar en este momento.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 overflow-x-auto">
                {{ $outputs->links() }}
            </div>
        </div>
    </div>

    <x-delete-modal
        modal-id="deleteModal"
        cancel-id="cancelDelete"
        confirm-id="confirmDelete"
        title="Eliminar registro"
        subtitle="Revise la información antes de confirmar."
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
    </script>
</body>

</html>