<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ $sale->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas text-foreground">
    <div class="mx-auto max-w-5xl p-3 sm:p-4">
        <div class="rounded-3xl bg-surface p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-success-soft text-3xl">
                    💰
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight text-foreground sm:text-4xl">
                        Venta #{{ $sale->id }}
                    </h1>
                    <p class="mt-2 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                        {{ $paymentOnly ? 'Registrar pago de la venta seleccionada.' : 'Detalle de la venta y registro de pagos.' }}
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div
                    class="mt-5 rounded-2xl border border-success-border bg-success-soft px-4 py-4 text-lg font-medium leading-relaxed text-success sm:text-xl">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if (session('receipt_payment_id'))
                <div
                    class="mt-4 rounded-2xl border border-warning-border bg-warning-soft px-4 py-4 text-lg font-medium leading-relaxed text-warning sm:text-xl">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            Recibo del pago listo para descargar.
                        </div>
                        <a href="{{ route('sales.payments.receipt', ['sale' => $sale, 'payment' => session('receipt_payment_id')]) }}"
                            class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition hover:bg-warning-hover">
                            <span class="text-2xl">📄</span>
                            <span>Recibo (PDF)</span>
                        </a>
                    </div>
                </div>
            @endif

            <div class="mt-5 flex justify-end">
                <div class="flex flex-col gap-3 sm:flex-row">
                    @if (! $paymentOnly)
                        <a href="{{ route('sales.note', $sale) }}"
                            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-primary px-5 py-3 text-lg font-bold text-primary-foreground transition hover:bg-primary-hover">
                            <span class="text-2xl">📄</span>
                            <span>Nota de venta (PDF)</span>
                        </a>

                        <a href="{{ route('sales.note.image', $sale) }}"
                            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-success px-5 py-3 text-lg font-bold text-success-foreground transition hover:bg-success-hover">
                            <span class="text-2xl">🖼️</span>
                            <span>Nota de venta (Imagen)</span>
                        </a>
                    @endif

                    @if ($paymentOnly)
                        <a href="{{ route('credits.index') }}"
                            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition hover:bg-warning-hover">
                            <span class="text-2xl">🔎</span>
                            <span>Volver a búsqueda</span>
                        </a>
                    @endif

                    <a href="{{ route('home') }}"
                        class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-muted px-5 py-3 text-lg font-bold text-foreground transition hover:bg-border">
                        <span class="text-2xl">🏠</span>
                        <span>Inicio</span>
                    </a>
                </div>
            </div>

        </div>

        @php
            $formatCents = fn(int $cents) => number_format($cents / 100, 2, '.', '');
        @endphp

        @if (! $paymentOnly)
            <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">👤 Cliente</span>
                    <span class="text-lg font-medium text-foreground sm:text-xl">
                        {{ $sale->customer_name }}
                    </span>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📍 Lugar de entrega</span>
                    <span class="text-lg font-medium text-foreground sm:text-xl">
                        {{ $sale->delivery_location ?? '—' }}
                    </span>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha de venta</span>
                    <span class="text-lg font-medium text-foreground sm:text-xl">
                        {{ $sale->created_at?->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Fecha prometida</span>
                    <span class="text-lg font-medium text-foreground sm:text-xl">
                        {{ $sale->due_date?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Total</span>
                    <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($totalCents) }}</span>
                </div>

                <div>
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Pagado</span>
                    <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($paidCents) }}</span>
                </div>

                <div class="md:col-span-2">
                    <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Saldo</span>
                    <span class="text-3xl font-extrabold text-foreground">{{ $formatCents($balanceCents) }}</span>
                </div>
                </div>
            </div>

            <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
                <div class="flex items-center gap-3">
                <span class="text-3xl">🧺</span>
                <div>
                    <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Productos</h2>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">Detalle por ítem.</p>
                </div>
                </div>

                <div class="mt-5 space-y-4">
                @foreach ($sale->items as $item)
                    <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧴 Producto</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">
                                    {{ $item->product?->name ?? 'Producto no disponible' }}
                                </span>
                            </div>
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🔢 Cantidad</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">{{ $item->quantity }}</span>
                            </div>
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💵 Precio</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">{{ $item->unit_price }}</span>
                            </div>
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧮 Subtotal</span>
                                <span class="text-lg font-extrabold text-foreground sm:text-xl">{{ $item->line_total }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>

            <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6">
                <div class="flex items-center gap-3">
                <span class="text-3xl">🧾</span>
                <div>
                    <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Pagos</h2>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">Historial de pagos registrados.</p>
                </div>
                </div>

                <div class="mt-5 space-y-4">
                @forelse ($sale->payments as $payment)
                    <div class="rounded-2xl border border-border bg-muted p-4 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">📅 Fecha</span>
                                <span class="text-lg font-medium text-foreground sm:text-xl">
                                    {{ $payment->paid_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Monto</span>
                                <span class="text-lg font-extrabold text-foreground sm:text-xl">
                                    {{ $payment->amount }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-border bg-muted p-4 text-lg text-foreground-muted shadow-sm">
                        Aún no hay pagos registrados.
                    </div>
                @endforelse
                </div>
            </div>
        @endif

        <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-center gap-3">
                <span class="text-3xl">➕</span>
                <div>
                    <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">Registrar pago</h2>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                        Si el pago no completa el total, ingrese la próxima fecha prometida.
                    </p>
                    <p class="mt-2 text-lg font-semibold text-foreground sm:text-xl">
                        Saldo actual:
                        <span class="text-2xl font-extrabold">{{ $formatCents($balanceCents) }}</span>
                    </p>
                </div>
            </div>

            @if ($balanceCents <= 0)
                <div class="mt-6 rounded-2xl border border-success-border bg-success-soft p-4 text-lg text-success sm:text-xl">
                    ✅ Esta venta ya está pagada.
                </div>
            @else
                <form method="POST" action="{{ route('sales.payments.store', $sale) }}" class="mt-6 space-y-6" novalidate>
                    @csrf
                    <input type="hidden" name="payment_only" value="{{ $paymentOnly ? '1' : '0' }}">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="amount" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                                <span class="text-2xl">💳</span>
                                <span>Monto del pago</span>
                            </label>
                            <input type="number" name="amount" id="amount" min="0.01" step="0.01" required
                                value="{{ old('amount') }}" placeholder="Ejemplo: 50"
                                class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                            @error('amount')
                                <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="next_due_date" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                                <span class="text-2xl">📅</span>
                                <span>Próxima fecha prometida <span class="font-normal text-foreground-muted">(si queda saldo)</span></span>
                            </label>
                            <input type="date" name="next_due_date" id="next_due_date" value="{{ old('next_due_date', $sale->due_date?->toDateString()) }}" min="{{ now()->toDateString() }}"
                                class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                            @error('next_due_date')
                                <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex min-h-[64px] w-full items-center justify-center gap-3 rounded-2xl bg-success px-6 py-4 text-xl font-bold text-success-foreground transition hover:bg-success-hover active:scale-[0.99] sm:text-2xl">
                        <span class="text-2xl">✅</span>
                        <span>Guardar pago</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <script>
        (function() {
            const amountInput = document.getElementById('amount');
            const nextDueDateInput = document.getElementById('next_due_date');

            if (!amountInput || !nextDueDateInput) {
                return;
            }

            const balanceCents = {{ (int) $balanceCents }};

            function moneyToCents(value) {
                const normalized = String(value ?? '').replace(',', '.').trim();
                if (!normalized) return 0;

                const parsed = Number(normalized);
                if (!Number.isFinite(parsed)) return 0;

                return Math.round(parsed * 100);
            }

            function recalcRequired() {
                if (!amountInput.value) {
                    nextDueDateInput.required = false;
                    return;
                }

                const amountCents = moneyToCents(amountInput.value);
                const newBalanceCents = balanceCents - amountCents;

                nextDueDateInput.required = newBalanceCents > 0;
            }

            amountInput.addEventListener('input', recalcRequired);
            amountInput.addEventListener('change', recalcRequired);

            recalcRequired();
        })();
    </script>
</body>

</html>
