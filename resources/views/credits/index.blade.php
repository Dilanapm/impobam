<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos pendientes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas text-foreground">
    <div class="mx-auto max-w-5xl p-3 sm:p-4">
        <div class="rounded-3xl bg-surface p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-warning-soft text-3xl">
                    💳
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight text-foreground sm:text-4xl">
                        Registrar pago (crédito)
                    </h1>
                    <p class="mt-2 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                        Ventas con saldo pendiente.
                    </p>
                </div>
            </div>
        </div>

        @php
            $formatCents = fn(int $cents) => number_format($cents / 100, 2, '.', '');
        @endphp

        <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Pendientes</h2>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                        {{ count($pendingSales) }} venta(s) con saldo.
                    </p>
                </div>

                <a href="{{ route('home') }}"
                    class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-muted px-5 py-3 text-lg font-bold text-foreground transition hover:bg-border">
                    <span class="text-2xl">↩️</span>
                    <span>Inicio</span>
                </a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($pendingSales as $row)
                    @php
                        /** @var \App\Models\Sale $sale */
                        $sale = $row['sale'];
                    @endphp

                    <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm">
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
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📍 Lugar de entrega</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->delivery_location ?? '—' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha prometida</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Total</span>
                                <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $formatCents($row['totalCents']) }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Pagado</span>
                                <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $formatCents($row['paidCents']) }}</span>
                            </div>

                            <div class="md:col-span-2">
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Saldo</span>
                                <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($row['balanceCents']) }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('sales.show', $sale) }}"
                                class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition hover:bg-warning-hover sm:text-xl">
                                <span class="text-2xl">✅</span>
                                <span>Registrar pago</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-border bg-muted p-4 text-lg text-foreground-muted shadow-sm">
                        No hay créditos/pagos pendientes.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>

</html>