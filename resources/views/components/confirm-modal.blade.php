@props([
    'modalId' => 'confirmModal',
    'title' => 'Confirmar acción',
    'subtitle' => 'Revise la información antes de continuar.',
    'cancelId' => 'cancelModal',
    'confirmId' => 'confirmModalAction',
    'cancelText' => 'Seguir editando',
    'confirmText' => 'Confirmar registro',
])

<div
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50 p-3 sm:items-center"
>
    <div class="w-full max-w-2xl rounded-3xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-center gap-3">
            <span class="text-3xl">✅</span>
            <div>
                <h3 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                    {{ $title }}
                </h3>
                <p class="mt-1 text-lg text-slate-500 sm:text-xl">
                    {{ $subtitle }}
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            {{ $slot }}
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
                type="button"
                id="{{ $cancelId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-slate-200 px-4 py-3 text-xl font-bold text-slate-800 transition hover:bg-slate-300"
            >
                <span class="text-2xl">✏️</span>
                <span>{{ $cancelText }}</span>
            </button>

            <button
                type="button"
                id="{{ $confirmId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-xl font-bold text-white transition hover:bg-emerald-700"
            >
                <span class="text-2xl">💾</span>
                <span>{{ $confirmText }}</span>
            </button>
        </div>
    </div>
</div>