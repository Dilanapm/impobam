<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de salida de productos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-5xl p-3 sm:p-4">
        <div class="rounded-3xl bg-white p-4 shadow-lg sm:p-6 md:p-8">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-3xl">
                    📦
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight text-slate-800 sm:text-4xl">
                        Registro de salida de productos
                    </h1>
                    <p class="mt-2 text-lg leading-relaxed text-slate-600 sm:text-xl">
                        Complete este formulario cada vez que retire mercadería del depósito.
                    </p>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">💡</span>
                    <h2 class="text-xl font-bold text-blue-900 sm:text-2xl">
                        Ayuda rápida
                    </h2>
                </div>

                <ol class="mt-4 space-y-3 text-lg leading-relaxed text-blue-900 sm:text-xl">
                    <li class="flex items-start gap-3">
                        <span class="text-2xl">1️⃣</span>
                        <span>Seleccione el producto.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-2xl">2️⃣</span>
                        <span>Escriba la cantidad retirada.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-2xl">3️⃣</span>
                        <span>Presione el botón <span class="font-bold">Registrar salida</span>.</span>
                    </li>
                </ol>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-lg font-medium leading-relaxed text-emerald-800 sm:text-xl">
                    ✅ {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('stock-outputs.store') }}" class="mt-6 space-y-6">
                @csrf

                <div>
                    <label for="product_id" class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                        <span class="text-2xl">🧴</span>
                        <span>Producto</span>
                    </label>
                    <select
                        name="product_id"
                        id="product_id"
                        required
                        class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl"
                    >
                        <option value="">Seleccione un producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-base text-slate-500 sm:text-lg">
                        Toque la lista y elija el producto retirado.
                    </p>
                    @error('product_id')
                        <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                        <span class="text-2xl">🔢</span>
                        <span>Cantidad retirada</span>
                    </label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        min="1"
                        value="{{ old('quantity') }}"
                        placeholder="Ejemplo: 5"
                        required
                        class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl"
                    >
                    <p class="mt-2 text-base text-slate-500 sm:text-lg">
                        Escriba cuántas unidades se sacaron.
                    </p>
                    @error('quantity')
                        <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="employee_name" class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                        <span class="text-2xl">👤</span>
                        <span>Nombre del empleado <span class="font-normal text-slate-500">(opcional)</span></span>
                    </label>
                    <input
                        type="text"
                        name="employee_name"
                        id="employee_name"
                        value="{{ old('employee_name') }}"
                        placeholder="Ejemplo: Juan Pérez"
                        class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl"
                    >
                    <p class="mt-2 text-base text-slate-500 sm:text-lg">
                        Puede dejar este espacio vacío si no desea escribir un nombre.
                    </p>
                    @error('employee_name')
                        <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                        <span class="text-2xl">📝</span>
                        <span>Observación <span class="font-normal text-slate-500">(opcional)</span></span>
                    </label>
                    <textarea
                        name="notes"
                        id="notes"
                        placeholder="Detalle adicional del retiro"
                        class="min-h-[140px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex min-h-[64px] w-full items-center justify-center gap-3 rounded-2xl bg-blue-600 px-6 py-4 text-xl font-bold text-white transition hover:bg-blue-700 active:scale-[0.99] sm:text-2xl"
                >
                    <span class="text-2xl">✅</span>
                    <span>Registrar salida</span>
                </button>
            </form>

            <div class="my-8 border-t-2 border-slate-200"></div>

            <div class="flex items-center gap-3">
                <span class="text-3xl">🕘</span>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                        Últimos registros
                    </h2>
                    <p class="mt-1 text-lg text-slate-500 sm:text-xl">
                        Aquí se muestran las últimas salidas registradas.
                    </p>
                </div>
            </div>

            <div class="mt-5 hidden overflow-x-auto lg:block">
                <table class="min-w-[900px] w-full border-collapse overflow-hidden rounded-2xl">
                    <thead>
                        <tr class="bg-slate-100 text-left text-base font-bold text-slate-700">
                            <th class="px-4 py-4">📅 Fecha</th>
                            <th class="px-4 py-4">🧴 Producto</th>
                            <th class="px-4 py-4">🔢 Cantidad</th>
                            <th class="px-4 py-4">👤 Empleado</th>
                            <th class="px-4 py-4">📝 Observación</th>
                            <th class="px-4 py-4">🗑️ Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-base text-slate-700">
                        @forelse($recentOutputs as $output)
                            <tr class="border-b border-slate-200 align-top">
                                <td class="px-4 py-4">{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4">{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                                <td class="px-4 py-4">{{ $output->quantity }}</td>
                                <td class="px-4 py-4">{{ $output->employee_name ?? '—' }}</td>
                                <td class="px-4 py-4">{{ $output->notes ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    @if ((int) $lastOutputId === (int) $output->id)
                                        <form method="POST"
                                            action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                                'stock-outputs.employee-destroy',
                                                now()->addMinutes(30),
                                                ['stockOutput' => $output->id],
                                            ) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-base font-bold text-white transition hover:bg-red-700"
                                                onclick="return confirm('¿Eliminar este último registro?')"
                                            >
                                                <span>🗑️</span>
                                                <span>Eliminar</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-lg text-slate-500">
                                    Aún no hay registros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5 space-y-4 lg:hidden">
                @forelse($recentOutputs as $output)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5">
                        <div class="space-y-4">
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📅 Fecha</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->moved_at?->format('d/m/Y H:i') }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🧴 Producto</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->product?->name ?? 'Producto no disponible' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🔢 Cantidad</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->quantity }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">👤 Empleado</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->employee_name ?? '—' }}</span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📝 Observación</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">{{ $output->notes ?? '—' }}</span>
                            </div>

                            <div class="pt-1">
                                @if ((int) $lastOutputId === (int) $output->id)
                                    <form method="POST"
                                        action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                            'stock-outputs.employee-destroy',
                                            now()->addMinutes(30),
                                            ['stockOutput' => $output->id],
                                        ) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-red-600 px-4 py-3 text-xl font-bold text-white transition hover:bg-red-700"
                                            onclick="return confirm('¿Eliminar este último registro?')"
                                        >
                                            <span class="text-2xl">🗑️</span>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-lg text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-lg text-slate-500 shadow-sm">
                        Aún no hay registros.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>

</html>