<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impobam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('stock-outputs.create') }}"
                class="group rounded-3xl bg-surface p-5 shadow-lg transition hover:bg-muted sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-3xl">
                        📦
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                            Registro de salidas
                        </h2>
                        <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                            Registre salidas de mercadería por producto.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-primary px-5 py-3 text-lg font-bold text-primary-foreground transition group-hover:bg-primary-hover sm:text-xl">
                    <span class="text-2xl">➡️</span>
                    <span>Entrar</span>
                </div>
            </a>

            <a href="{{ route('sales.create') }}"
                class="group rounded-3xl bg-surface p-5 shadow-lg transition hover:bg-muted sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-success-soft text-3xl">
                        💰
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                            Registrar venta
                        </h2>
                        <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                            Ingrese y registre una venta.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-success px-5 py-3 text-lg font-bold text-success-foreground transition group-hover:bg-success-hover sm:text-xl">
                    <span class="text-2xl">➡️</span>
                    <span>Entrar</span>
                </div>
            </a>

            <a href="{{ route('credits.index') }}"
                class="group rounded-3xl bg-surface p-5 shadow-lg transition hover:bg-muted sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-warning-soft text-3xl">
                        💳
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                            Registrar pago (crédito)
                        </h2>
                        <p class="mt-1 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                            Ver entregas con pagos pendientes.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-warning px-5 py-3 text-lg font-bold text-warning-foreground transition group-hover:bg-warning-hover sm:text-xl">
                    <span class="text-2xl">➡️</span>
                    <span>Entrar</span>
                </div>
            </a>
        </div>
    </div>
</body>

</html>
