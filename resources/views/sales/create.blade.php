<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrar venta</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <livewire:styles />
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
                        Registrar venta
                    </h1>
                    <p class="mt-2 text-lg leading-relaxed text-foreground-muted sm:text-xl">
                        Agregue varios productos, defina precios por ítem y registre pagos parciales.
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div
                    class="mt-5 rounded-2xl border border-success-border bg-success-soft px-4 py-4 text-lg font-medium leading-relaxed text-success sm:text-xl">
                    ✅ {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="mt-5 rounded-3xl bg-surface p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🧾</span>
                <div>
                    <h2 class="text-2xl font-extrabold text-foreground sm:text-3xl">
                        Nueva venta
                    </h2>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                        El total se calcula automáticamente.
                    </p>
                </div>
            </div>
            <livewire:sale-create-form />
        </div>
    </div>

    <livewire:scripts />
</body>

</html>
