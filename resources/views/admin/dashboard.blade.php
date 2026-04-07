<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <livewire:styles />
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
                <span class="text-3xl">🧾</span>
                <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                    Reportes (PDF)
                </h2>
            </div>

            <p class="mt-2 text-lg text-foreground-muted sm:text-xl">
                Descargue un reporte entendible con resumen y detalle.
            </p>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-border bg-muted p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xl font-extrabold text-foreground sm:text-2xl">💰 Ventas</div>
                        <div class="grid grid-cols-3 gap-2">
                            <a href="{{ route('admin.reports.sales', ['range' => 'hoy']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-success px-4 py-2 text-base font-bold text-success-foreground transition hover:bg-success-hover">
                                Hoy
                            </a>
                            <a href="{{ route('admin.reports.sales', ['range' => 'semana']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-success px-4 py-2 text-base font-bold text-success-foreground transition hover:bg-success-hover">
                                Semana
                            </a>
                            <a href="{{ route('admin.reports.sales', ['range' => 'mes']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-success px-4 py-2 text-base font-bold text-success-foreground transition hover:bg-success-hover">
                                Mes
                            </a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.reports.sales') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3" novalidate>
                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Desde</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                        </div>

                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Hasta</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-success px-4 py-2 text-base font-bold text-success-foreground transition hover:bg-success-hover">
                                <span>📄</span>
                                <span>Descargar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-border bg-muted p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xl font-extrabold text-foreground sm:text-2xl">📦 Salidas</div>
                        <div class="grid grid-cols-3 gap-2">
                            <a href="{{ route('admin.reports.outputs', ['range' => 'hoy']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-primary px-4 py-2 text-base font-bold text-primary-foreground transition hover:bg-primary-hover">
                                Hoy
                            </a>
                            <a href="{{ route('admin.reports.outputs', ['range' => 'semana']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-primary px-4 py-2 text-base font-bold text-primary-foreground transition hover:bg-primary-hover">
                                Semana
                            </a>
                            <a href="{{ route('admin.reports.outputs', ['range' => 'mes']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-primary px-4 py-2 text-base font-bold text-primary-foreground transition hover:bg-primary-hover">
                                Mes
                            </a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.reports.outputs') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3" novalidate>
                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Desde</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20">
                        </div>

                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Hasta</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-2 text-base font-bold text-primary-foreground transition hover:bg-primary-hover">
                                <span>📄</span>
                                <span>Descargar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-border bg-muted p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xl font-extrabold text-foreground sm:text-2xl">💳 Pagos pendientes</div>
                        <div class="grid grid-cols-3 gap-2">
                            <a href="{{ route('admin.reports.pending-payments', ['range' => 'hoy']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-warning px-4 py-2 text-base font-bold text-warning-foreground transition hover:bg-warning-hover">
                                Hoy
                            </a>
                            <a href="{{ route('admin.reports.pending-payments', ['range' => 'semana']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-warning px-4 py-2 text-base font-bold text-warning-foreground transition hover:bg-warning-hover">
                                Semana
                            </a>
                            <a href="{{ route('admin.reports.pending-payments', ['range' => 'mes']) }}"
                                class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-warning px-4 py-2 text-base font-bold text-warning-foreground transition hover:bg-warning-hover">
                                Mes
                            </a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.reports.pending-payments') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3" novalidate>
                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Desde</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-warning focus:ring-4 focus:ring-warning/20">
                        </div>

                        <div>
                            <label class="mb-2 block text-base font-bold text-foreground">Hasta</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="min-h-[52px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-2 text-base shadow-sm outline-none transition focus:border-warning focus:ring-4 focus:ring-warning/20">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-warning px-4 py-2 text-base font-bold text-warning-foreground transition hover:bg-warning-hover">
                                <span>📄</span>
                                <span>Descargar</span>
                            </button>
                        </div>
                    </form>
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

        <livewire:admin-outputs-history
            :product-id="request('product_id')"
            :start-date="request('start_date')"
            :end-date="request('end_date')"
        />

        <livewire:admin-sales-history />
    </div>

    <livewire:scripts />
</body>

</html>
