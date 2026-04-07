<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créditos pendientes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <livewire:styles />
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

        <livewire:credit-payment-search />
    </div>

    <livewire:scripts />
</body>

</html>
