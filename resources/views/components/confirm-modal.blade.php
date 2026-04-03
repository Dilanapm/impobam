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
    <div class="w-full max-w-2xl rounded-3xl bg-surface p-5 shadow-2xl sm:p-6">
        <div class="flex items-center gap-3">
            <span class="text-3xl">✅</span>
            <div>
                <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                    {{ $title }}
                </h3>
                <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                    {{ $subtitle }}
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-4 rounded-2xl border border-border bg-muted p-4">
            {{ $slot }}
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
                type="button"
                id="{{ $cancelId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-muted px-4 py-3 text-xl font-bold text-foreground transition hover:bg-border"
            >
                <span class="text-2xl">✏️</span>
                <span>{{ $cancelText }}</span>
            </button>

            <button
                type="button"
                id="{{ $confirmId }}"
                class="inline-flex min-h-[60px] items-center justify-center gap-2 rounded-2xl bg-success px-4 py-3 text-xl font-bold text-success-foreground transition hover:bg-success-hover"
            >
                <span class="text-2xl">💾</span>
                <span>{{ $confirmText }}</span>
            </button>
        </div>
    </div>
</div>