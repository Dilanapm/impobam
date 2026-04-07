<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Impobam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <livewire:styles />
    <style>
        summary::-webkit-details-marker {
            display: none;
        }

        summary {
            list-style: none;
        }
    </style>
</head>

<body class="bg-canvas text-foreground">
    <div class="mx-auto max-w-5xl p-3 sm:p-4">
        <div class="rounded-3xl bg-surface p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-soft text-3xl">
                    🏭
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight text-foreground sm:text-4xl">
                        Impobam
                    </h1>
                    <p class="mt-2 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                        Seleccione una opción para continuar.
                    </p>
                </div>
            </div>
        </div>

        @php
            $openSales = trim((string) request()->query('sale_note_query', '')) !== '';
        @endphp

        <div class="mt-5 space-y-4">
            <details class="rounded-3xl bg-surface shadow-lg" @if ($openSales) open @endif>
                <summary class="cursor-pointer rounded-3xl p-5 transition hover:bg-muted sm:p-6" aria-label="Ventas">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-success-soft text-3xl">
                            💰
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Ventas</h2>
                            <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                                Registrar ventas, pagos y notas de venta.
                            </p>
                        </div>
                        <div class="text-2xl text-foreground-muted">⬇️</div>
                    </div>
                </summary>

                <div class="px-5 pb-5 sm:px-6 sm:pb-6">
                    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <a href="{{ route('sales.create') }}"
                            class="group rounded-3xl border border-border bg-muted p-5 shadow-sm transition hover:bg-surface sm:p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-success-soft text-3xl">
                                    🧾
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-foreground sm:text-2xl">Registrar venta</h3>
                                    <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">Ingrese y registre una venta.</p>
                                </div>
                            </div>

                            <div
                                class="mt-5 inline-flex min-h-[56px] w-full items-center justify-center gap-3 rounded-2xl bg-success px-5 py-3 text-lg font-bold text-success-foreground transition group-hover:bg-success-hover sm:text-xl">
                                <span class="text-2xl">➡️</span>
                                <span>Entrar</span>
                            </div>
                        </a>

                        <a href="{{ route('credits.index') }}"
                            class="group rounded-3xl border border-border bg-muted p-5 shadow-sm transition hover:bg-surface sm:p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-warning-soft text-3xl">
                                    💳
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-foreground sm:text-2xl">Registrar pago</h3>
                                    <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">Ver entregas con pagos pendientes.</p>
                                </div>
                            </div>

                            <div
                                class="mt-5 inline-flex min-h-[56px] w-full items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition group-hover:bg-warning-hover sm:text-xl">
                                <span class="text-2xl">➡️</span>
                                <span>Entrar</span>
                            </div>
                        </a>
                    </div>

                    <div class="mt-4 rounded-3xl border border-border bg-muted p-5 shadow-sm sm:p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-3xl">
                                📄
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-foreground sm:text-2xl">Generar nota de venta</h3>
                                <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                                    Busque una venta ya registrada por cliente y descargue la nota en PDF.
                                </p>
                            </div>
                        </div>

                        <livewire:sale-note-search />
                    </div>
                </div>
            </details>

            <details class="rounded-3xl bg-surface shadow-lg">
                <summary class="cursor-pointer rounded-3xl p-5 transition hover:bg-muted sm:p-6" aria-label="Salidas">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-3xl">
                            📦
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Salidas</h2>
                            <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                                Registro de salidas de mercadería por producto.
                            </p>
                        </div>
                        <div class="text-2xl text-foreground-muted">⬇️</div>
                    </div>
                </summary>

                <div class="px-5 pb-5 sm:px-6 sm:pb-6">
                    <a href="{{ route('stock-outputs.create') }}"
                        class="group mt-5 block rounded-3xl border border-border bg-muted p-5 shadow-sm transition hover:bg-surface sm:p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-3xl">
                                ➕
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-foreground sm:text-2xl">Registro de salidas</h3>
                                <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                                    Registre salidas de mercadería por producto.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 inline-flex min-h-[56px] w-full items-center justify-center gap-3 rounded-2xl bg-primary px-5 py-3 text-lg font-bold text-primary-foreground transition group-hover:bg-primary-hover sm:text-xl">
                            <span class="text-2xl">➡️</span>
                            <span>Entrar</span>
                        </div>
                    </a>
                </div>
            </details>
        </div>
    </div>

    <livewire:scripts />
</body>

</html>
