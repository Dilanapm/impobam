@props([
    'modalId' => 'deleteModal',
    'title' => 'Eliminar registro',
    'subtitle' => 'Esta acción no se puede deshacer.',
    'cancelId' => 'cancelDelete',
    'confirmId' => 'confirmDelete',
])

<div
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50 p-3 sm:items-center"
>
    <div class="w-full max-w-2xl rounded-3xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-center gap-3">
            <span class="text-3xl">🗑️</span>
            <div>
                <h3 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                    {{ $title }}
                </h3>
                <p class="mt-1 text-lg text-slate-500 sm:text-xl">
                    {{ $subtitle }}
                </p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-lg text-slate-700 sm:text-xl">
                ¿Está seguro que desea eliminar este registro?
            </p>

            <div class="mt-4 space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">Producto</span>
                    <p id="deletePreviewProduct" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">Cantidad</span>
                    <p id="deletePreviewQuantity" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">Fecha</span>
                    <p id="deletePreviewDate" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
                </div>
            </div>
        </div>

        <form id="deleteModalForm" method="POST" class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            @csrf
            @method('DELETE')

            <button
                type="button"
                id="{{ $cancelId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-slate-200 px-4 py-3 text-xl font-bold text-slate-800 transition hover:bg-slate-300"
            >
                <span class="text-2xl">↩️</span>
                <span>Cancelar</span>
            </button>

            <button
                type="submit"
                id="{{ $confirmId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 py-3 text-xl font-bold text-white transition hover:bg-red-700"
            >
                <span class="text-2xl">🗑️</span>
                <span>Eliminar</span>
            </button>
        </form>
    </div>
</div>