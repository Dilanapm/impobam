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

        {{-- ENCABEZADO --}}
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
                        Registre cada salida de mercadería de forma simple y clara.
                    </p>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">💡</span>
                    <h2 class="text-xl font-bold text-blue-900 sm:text-2xl">
                        Cómo usar esta pantalla
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
                        <span>Presione <strong>Revisar registro</strong>.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-2xl">4️⃣</span>
                        <span>Revise los datos y luego toque <strong>Confirmar registro</strong>.</span>
                    </li>
                </ol>
            </div>

            @if (session('status'))
                <div
                    class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-lg font-medium leading-relaxed text-emerald-800 sm:text-xl">
                    ✅ {{ session('status') }}
                </div>
            @endif
        </div>

        {{-- ULTIMOS 5 REGISTROS ARRIBA --}}
        <div class="mt-5 rounded-3xl bg-white p-4 shadow-lg sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🕘</span>
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                            Últimos 5 registros
                        </h2>
                        <p class="mt-1 text-lg text-slate-500 sm:text-xl">
                            Aquí puede ver rápidamente las últimas salidas guardadas.
                        </p>
                    </div>
                </div>

                <button type="button" id="toggleRecordsButton" aria-expanded="false"
                    class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-slate-200 px-5 py-3 text-lg font-bold text-slate-800 transition hover:bg-slate-300">
                    <span id="toggleRecordsIcon" class="text-2xl">⬇️</span>
                    <span id="toggleRecordsText">Mostrar registros</span>
                </button>
            </div>

            <div id="recordsContainer" class="mt-5 hidden space-y-4">
                @forelse($recentOutputs as $output)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📅
                                    Fecha</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">
                                    {{ $output->moved_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🧴
                                    Producto</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">
                                    {{ $output->product?->name ?? 'Producto no disponible' }}
                                </span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🔢
                                    Cantidad</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">
                                    {{ $output->quantity }}
                                </span>
                            </div>

                            <div>
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">👤
                                    Empleado</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">
                                    {{ $output->employee_name ?? '—' }}
                                </span>
                            </div>

                            <div class="md:col-span-2">
                                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📝
                                    Observación</span>
                                <span class="text-lg font-medium text-slate-800 sm:text-xl">
                                    {{ $output->notes ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            @if ((int) $lastOutputId === (int) $output->id)
                                <button type="button"
                                    class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-red-600 px-4 py-3 text-xl font-bold text-white transition hover:bg-red-700 open-delete-modal"
                                    data-action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                        'stock-outputs.employee-destroy',
                                        now()->addMinutes(30),
                                        ['stockOutput' => $output->id],
                                    ) }}"
                                    data-product="{{ $output->product?->name ?? 'Producto no disponible' }}"
                                    data-quantity="{{ $output->quantity }}"
                                    data-date="{{ $output->moved_at?->format('d/m/Y H:i') }}">
                                    <span class="text-2xl">🗑️</span>
                                    <span>Eliminar último registro</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-lg text-slate-500 shadow-sm">
                        Aún no hay registros.
                    </div>
                @endforelse
            </div>

            {{-- FORMULARIO ABAJO --}}
            <div class="mt-5 rounded-3xl bg-white p-4 shadow-lg sm:p-6 md:p-8">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📝</span>
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                            Nuevo registro
                        </h2>
                        <p class="mt-1 text-lg text-slate-500 sm:text-xl">
                            Llene los datos y luego revise antes de guardar.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('stock-outputs.store') }}" class="mt-6 space-y-6"
                    id="stockOutputForm">
                    @csrf

                    <div>
                        <label for="product_id"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                            <span class="text-2xl">🧴</span>
                            <span>Producto</span>
                        </label>
                        <select name="product_id" id="product_id" required
                            class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl">
                            <option value="">Seleccione un producto</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                            <span class="text-2xl">🔢</span>
                            <span>Cantidad retirada</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" min="1"
                            value="{{ old('quantity') }}" placeholder="Ejemplo: 5" required
                            class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl">
                        @error('quantity')
                            <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="employee_name"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                            <span class="text-2xl">👤</span>
                            <span>Nombre del empleado <span class="font-normal text-slate-500">(opcional)</span></span>
                        </label>
                        <input type="text" name="employee_name" id="employee_name"
                            value="{{ old('employee_name') }}" placeholder="Ejemplo: Juan Pérez"
                            class="min-h-[64px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl">
                        @error('employee_name')
                            <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="notes"
                            class="mb-3 flex items-center gap-2 text-xl font-bold text-slate-800 sm:text-2xl">
                            <span class="text-2xl">📝</span>
                            <span>Observación <span class="font-normal text-slate-500">(opcional)</span></span>
                        </label>
                        <textarea name="notes" id="notes" placeholder="Detalle adicional del retiro"
                            class="min-h-[140px] w-full rounded-2xl border-2 border-slate-300 bg-white px-4 py-3 text-lg text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-200 sm:text-xl">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="mt-2 text-lg font-medium text-red-600">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <button type="button" id="previewButton"
                        class="inline-flex min-h-[64px] w-full items-center justify-center gap-3 rounded-2xl bg-blue-600 px-6 py-4 text-xl font-bold text-white transition hover:bg-blue-700 active:scale-[0.99] sm:text-2xl">
                        <span class="text-2xl">👀</span>
                        <span>Revisar registro</span>
                    </button>
                </form>
            </div>
        </div>

        <x-confirm-modal modal-id="previewModal" cancel-id="cancelPreview" confirm-id="confirmSubmit"
            title="Confirmar registro" subtitle="Revise los datos antes de guardar.">
            <div>
                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🧴 Producto</span>
                <p id="previewProduct" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🔢 Cantidad</span>
                <p id="previewQuantity" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">👤 Empleado</span>
                <p id="previewEmployee" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">📝 Observación</span>
                <p id="previewNotes" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-slate-500">🕒 Se guardará
                    con</span>
                <p id="previewDateTime" class="text-xl font-semibold text-slate-800 sm:text-2xl">—</p>
            </div>
        </x-confirm-modal>

        <x-delete-modal modal-id="deleteModal" cancel-id="cancelDelete" confirm-id="confirmDelete"
            title="Eliminar registro" subtitle="Revise la información antes de confirmar." />
        <script>
            const previewButton = document.getElementById('previewButton');
            const previewModal = document.getElementById('previewModal');
            const cancelPreview = document.getElementById('cancelPreview');
            const confirmSubmit = document.getElementById('confirmSubmit');
            const form = document.getElementById('stockOutputForm');

            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const employeeInput = document.getElementById('employee_name');
            const notesInput = document.getElementById('notes');

            const previewProduct = document.getElementById('previewProduct');
            const previewQuantity = document.getElementById('previewQuantity');
            const previewEmployee = document.getElementById('previewEmployee');
            const previewNotes = document.getElementById('previewNotes');
            const previewDateTime = document.getElementById('previewDateTime');

            previewButton.addEventListener('click', () => {
                if (!productSelect.value) {
                    alert('Seleccione un producto.');
                    productSelect.focus();
                    return;
                }

                if (!quantityInput.value || Number(quantityInput.value) < 1) {
                    alert('Ingrese una cantidad válida.');
                    quantityInput.focus();
                    return;
                }

                const productText = productSelect.options[productSelect.selectedIndex].text;
                const quantityText = quantityInput.value;
                const employeeText = employeeInput.value.trim() !== '' ? employeeInput.value.trim() : 'Sin nombre';
                const notesText = notesInput.value.trim() !== '' ? notesInput.value.trim() : 'Sin observación';

                const now = new Date();
                const formattedNow = now.toLocaleString('es-BO', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                previewProduct.textContent = productText;
                previewQuantity.textContent = quantityText;
                previewEmployee.textContent = employeeText;
                previewNotes.textContent = notesText;
                previewDateTime.textContent = formattedNow;

                previewModal.classList.remove('hidden');
                previewModal.classList.add('flex');
            });

            cancelPreview.addEventListener('click', () => {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
            });

            confirmSubmit.addEventListener('click', () => {
                form.submit();
            });

            previewModal.addEventListener('click', (e) => {
                if (e.target === previewModal) {
                    previewModal.classList.add('hidden');
                    previewModal.classList.remove('flex');
                }
            });
            const toggleRecordsButton = document.getElementById('toggleRecordsButton');
            const recordsContainer = document.getElementById('recordsContainer');
            const toggleRecordsText = document.getElementById('toggleRecordsText');
            const toggleRecordsIcon = document.getElementById('toggleRecordsIcon');

            toggleRecordsButton.addEventListener('click', () => {
                const isHidden = recordsContainer.classList.contains('hidden');

                if (isHidden) {
                    recordsContainer.classList.remove('hidden');
                    toggleRecordsText.textContent = 'Ocultar registros';
                    toggleRecordsIcon.textContent = '⬆️';
                    toggleRecordsButton.setAttribute('aria-expanded', 'true');
                } else {
                    recordsContainer.classList.add('hidden');
                    toggleRecordsText.textContent = 'Mostrar registros';
                    toggleRecordsIcon.textContent = '⬇️';
                    toggleRecordsButton.setAttribute('aria-expanded', 'false');
                }
            });
            const deleteModal = document.getElementById('deleteModal');
            const deleteModalForm = document.getElementById('deleteModalForm');
            const cancelDelete = document.getElementById('cancelDelete');

            const deletePreviewProduct = document.getElementById('deletePreviewProduct');
            const deletePreviewQuantity = document.getElementById('deletePreviewQuantity');
            const deletePreviewDate = document.getElementById('deletePreviewDate');

            document.querySelectorAll('.open-delete-modal').forEach(button => {
                button.addEventListener('click', () => {
                    const action = button.dataset.action;
                    const product = button.dataset.product;
                    const quantity = button.dataset.quantity;
                    const date = button.dataset.date;

                    deleteModalForm.action = action;
                    deletePreviewProduct.textContent = product || '—';
                    deletePreviewQuantity.textContent = quantity || '—';
                    deletePreviewDate.textContent = date || '—';

                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');
                });
            });

            cancelDelete.addEventListener('click', () => {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
            });

            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) {
                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                }
            });
        </script>

</body>

</html>
