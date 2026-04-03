<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar venta</title>
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

            @if ($errors->any())
                <div
                    class="mt-5 rounded-2xl border border-danger-border bg-danger-soft px-4 py-4 text-lg font-medium leading-relaxed text-danger sm:text-xl">
                    ⚠️ Revise los campos marcados.
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

            @php
                $oldItems = old('items');

                if (!is_array($oldItems) || count($oldItems) === 0) {
                    $oldItems = [
                        ['product_id' => '', 'quantity' => 1, 'unit_price' => ''],
                    ];
                }
            @endphp

            <form method="POST" action="{{ route('sales.store') }}" class="mt-6 space-y-6" id="saleForm" novalidate>
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="customer_name"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                            <span class="text-2xl">👤</span>
                            <span>Nombre del cliente</span>
                        </label>
                        <input type="text" name="customer_name" id="customer_name" required
                            value="{{ old('customer_name') }}" placeholder="Ejemplo: María Gómez"
                            class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                        @error('customer_name')
                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="delivery_location"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                            <span class="text-2xl">📍</span>
                            <span>Lugar de entrega <span class="font-normal text-foreground-muted">(opcional)</span></span>
                        </label>
                        <input type="text" name="delivery_location" id="delivery_location"
                            value="{{ old('delivery_location') }}" placeholder="Ejemplo: Depósito Central"
                            class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                        @error('delivery_location')
                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="rounded-3xl border border-border bg-muted p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">Productos</h3>
                            <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                                Agregue los productos incluidos en la venta.
                            </p>
                        </div>

                        <button type="button" id="addItemButton"
                            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-success px-5 py-3 text-lg font-bold text-success-foreground transition hover:bg-success-hover">
                            <span class="text-2xl">➕</span>
                            <span>Agregar producto</span>
                        </button>
                    </div>

                    <div id="itemsContainer" class="mt-6 space-y-4">
                        @foreach ($oldItems as $index => $item)
                            <div class="sale-item-row rounded-2xl border border-border bg-surface p-4 shadow-sm"
                                data-index="{{ $index }}">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <div class="md:col-span-5">
                                        <label
                                            class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧴
                                            Producto</label>
                                        <select name="items[{{ $index }}][product_id]" required
                                            class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                            <option value="">Seleccione un producto</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ (string) ($item['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id")
                                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label
                                            class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🔢
                                            Cantidad</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" min="1"
                                            value="{{ $item['quantity'] ?? 1 }}" required
                                            class="quantity-input min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                        @error("items.$index.quantity")
                                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-3">
                                        <label
                                            class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💵
                                            Precio</label>
                                        <input type="number" name="items[{{ $index }}][unit_price]" min="0" step="0.01"
                                            value="{{ $item['unit_price'] ?? '' }}" required
                                            class="price-input min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                        @error("items.$index.unit_price")
                                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label
                                            class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧮
                                            Subtotal</label>
                                        <div
                                            class="line-total flex min-h-[56px] items-center rounded-2xl border border-border bg-muted px-4 text-lg font-bold text-foreground">
                                            0.00
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button type="button"
                                        class="remove-item-button inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-danger px-5 py-3 text-lg font-bold text-danger-foreground transition hover:bg-danger-hover">
                                        <span class="text-2xl">🗑️</span>
                                        <span>Quitar</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('items')
                        <div class="mt-4 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div class="rounded-3xl border border-border bg-muted p-4 sm:p-6">
                    <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">Pago</h3>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                        Si queda saldo pendiente, ingrese una fecha prometida.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="initial_payment_amount"
                                class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                                <span class="text-2xl">💳</span>
                                <span>Pago inicial <span class="font-normal text-foreground-muted">(opcional)</span></span>
                            </label>
                            <input type="number" name="initial_payment_amount" id="initial_payment_amount" min="0"
                                step="0.01" value="{{ old('initial_payment_amount') }}" placeholder="Ejemplo: 100"
                                class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                            @error('initial_payment_amount')
                                <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date"
                                class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                                <span class="text-2xl">📅</span>
                                <span>Fecha prometida <span class="font-normal text-foreground-muted">(si queda saldo)</span></span>
                            </label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" min="{{ now()->toDateString() }}"
                                class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                            @error('due_date')
                                <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-border bg-surface p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <span
                                    class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾
                                    Total</span>
                                <span id="totalDisplay" class="text-2xl font-extrabold text-foreground">0.00</span>
                            </div>
                            <div>
                                <span
                                    class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳
                                    Pagado</span>
                                <span id="paidDisplay" class="text-2xl font-extrabold text-foreground">0.00</span>
                            </div>
                            <div>
                                <span
                                    class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳
                                    Saldo</span>
                                <span id="balanceDisplay" class="text-2xl font-extrabold text-foreground">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="inline-flex min-h-[64px] w-full items-center justify-center gap-3 rounded-2xl bg-success px-6 py-4 text-xl font-bold text-success-foreground transition hover:bg-success-hover active:scale-[0.99] sm:text-2xl">
                    <span class="text-2xl">✅</span>
                    <span>Guardar venta</span>
                </button>
            </form>
        </div>
    </div>

    <template id="itemRowTemplate">
        <div class="sale-item-row rounded-2xl border border-border bg-surface p-4 shadow-sm" data-index="__INDEX__">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <div class="md:col-span-5">
                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧴
                        Producto</label>
                    <select name="items[__INDEX__][product_id]" required
                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                        <option value="">Seleccione un producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🔢
                        Cantidad</label>
                    <input type="number" name="items[__INDEX__][quantity]" min="1" value="1" required
                        class="quantity-input min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                </div>

                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💵
                        Precio</label>
                    <input type="number" name="items[__INDEX__][unit_price]" min="0" step="0.01" value="" required
                        class="price-input min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧮
                        Subtotal</label>
                    <div class="line-total flex min-h-[56px] items-center rounded-2xl border border-border bg-muted px-4 text-lg font-bold text-foreground">
                        0.00
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button"
                    class="remove-item-button inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-danger px-5 py-3 text-lg font-bold text-danger-foreground transition hover:bg-danger-hover">
                    <span class="text-2xl">🗑️</span>
                    <span>Quitar</span>
                </button>
            </div>
        </div>
    </template>

    <script>
        (function() {
            const itemsContainer = document.getElementById('itemsContainer');
            const addItemButton = document.getElementById('addItemButton');
            const itemRowTemplate = document.getElementById('itemRowTemplate');
            const totalDisplay = document.getElementById('totalDisplay');
            const paidDisplay = document.getElementById('paidDisplay');
            const balanceDisplay = document.getElementById('balanceDisplay');
            const initialPaymentInput = document.getElementById('initial_payment_amount');
            const dueDateInput = document.getElementById('due_date');

            function toNumber(value) {
                const normalized = String(value ?? '').replace(',', '.');
                const parsed = parseFloat(normalized);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function formatMoney(value) {
                return value.toFixed(2);
            }

            function recalcRow(row) {
                const quantityInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.price-input');
                const lineTotalEl = row.querySelector('.line-total');

                const quantity = Math.max(0, parseInt(quantityInput?.value ?? '0', 10) || 0);
                const unitPrice = Math.max(0, toNumber(priceInput?.value ?? 0));
                const lineTotal = quantity * unitPrice;

                if (lineTotalEl) {
                    lineTotalEl.textContent = formatMoney(lineTotal);
                }

                return lineTotal;
            }

            function recalcAll() {
                let total = 0;
                itemsContainer.querySelectorAll('.sale-item-row').forEach((row) => {
                    total += recalcRow(row);
                });

                const paid = Math.max(0, toNumber(initialPaymentInput?.value ?? 0));
                const balance = Math.max(0, total - paid);

                totalDisplay.textContent = formatMoney(total);
                paidDisplay.textContent = formatMoney(paid);
                balanceDisplay.textContent = formatMoney(balance);

                if (dueDateInput) {
                    dueDateInput.required = balance > 0;
                }
            }

            function reindexRows() {
                const rows = Array.from(itemsContainer.querySelectorAll('.sale-item-row'));

                rows.forEach((row, newIndex) => {
                    row.dataset.index = String(newIndex);
                    row.querySelectorAll('select, input').forEach((el) => {
                        if (!el.name) return;
                        el.name = el.name.replace(/items\[\d+\]/, `items[${newIndex}]`);
                    });
                });
            }

            function addRow() {
                const index = itemsContainer.querySelectorAll('.sale-item-row').length;
                const html = itemRowTemplate.innerHTML.replace(/__INDEX__/g, String(index));
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const row = wrapper.firstElementChild;

                itemsContainer.appendChild(row);
                bindRow(row);
                recalcAll();
            }

            function removeRow(row) {
                const rows = itemsContainer.querySelectorAll('.sale-item-row');
                if (rows.length <= 1) return;
                row.remove();
                reindexRows();
                recalcAll();
            }

            function bindRow(row) {
                row.querySelectorAll('input, select').forEach((el) => {
                    el.addEventListener('input', recalcAll);
                    el.addEventListener('change', recalcAll);
                });

                const removeButton = row.querySelector('.remove-item-button');
                if (removeButton) {
                    removeButton.addEventListener('click', () => removeRow(row));
                }
            }

            itemsContainer.querySelectorAll('.sale-item-row').forEach(bindRow);
            addItemButton.addEventListener('click', addRow);
            initialPaymentInput.addEventListener('input', recalcAll);
            initialPaymentInput.addEventListener('change', recalcAll);

            recalcAll();
        })();
    </script>
</body>

</html>
