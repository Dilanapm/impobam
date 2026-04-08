<?php

use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    private const GUIDED_STEP_MIN = 1;
    private const GUIDED_STEP_MAX = 7;

    /**
     * @var array<int, array{id:int, name:string}>
     */
    public array $products = [];

    public string $customer_name = '';
    public string $delivery_location = '';
    public string $initial_payment_amount = '';
    public string $due_date = '';

    /**
     * @var array<int, array{key:string, product_id:string, quantity:int|string, unit_price:string}>
     */
    public array $items = [];

    public bool $guided_mode = false;
    public int $guided_step = 1;

    /** @var 'cash'|'credit'|null */
    public ?string $payment_type = null;
    public bool $auto_cash_filled = false;
    public bool $payment_type_locked = false;
    public bool $show_item_added_notice = false;

    public function mount(): void
    {
        $this->products = Product::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['ballerina acondicionador'])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
            ])
            ->all();

        $this->customer_name = (string) old('customer_name', '');
        $this->delivery_location = (string) old('delivery_location', '');
        $this->initial_payment_amount = (string) old('initial_payment_amount', '');
        $this->due_date = (string) old('due_date', '');

        $oldItems = old('items');

        if (! is_array($oldItems)) {
            $oldItems = [];
        }

        $this->items = array_values(array_map(function ($item) {
            if (! is_array($item)) {
                $item = [];
            }

            return [
                'key' => (string) Str::uuid(),
                'product_id' => (string) ($item['product_id'] ?? ''),
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => (string) ($item['unit_price'] ?? ''),
            ];
        }, $oldItems));

        $this->guided_mode = (string) old('_guided_mode', '0') === '1';
        $this->guided_step = (int) old('_guided_step', self::GUIDED_STEP_MIN);
        $this->guided_step = max(self::GUIDED_STEP_MIN, min(self::GUIDED_STEP_MAX, $this->guided_step));

        if (! $this->guided_mode) {
            $this->guided_step = self::GUIDED_STEP_MIN;
        }

        $this->inferPaymentType();

        if ($this->guided_mode) {
            $errorStep = $this->firstErrorStepFromSession();
            if ($errorStep !== null) {
                $this->guided_step = $errorStep;
            }
        }
    }

    public function addItem(): void
    {
        array_unshift($this->items, ['key' => (string) Str::uuid(), 'product_id' => '', 'quantity' => 1, 'unit_price' => '']);
        $this->show_item_added_notice = true;

        if (! $this->payment_type_locked) {
            $this->inferPaymentType();
        }
    }

    public function clearItemAddedNotice(): void
    {
        $this->show_item_added_notice = false;
    }

    public function removeItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->payment_type === 'cash' && $this->auto_cash_filled) {
            $this->initial_payment_amount = $this->formatCents($this->totalCents());
        }

        if (! $this->payment_type_locked) {
            $this->inferPaymentType();
        }
    }

    public function setPaymentType(string $type): void
    {
        if (! in_array($type, ['cash', 'credit'], true)) {
            return;
        }

        $this->payment_type = $type;
        $this->payment_type_locked = true;

        if ($type === 'cash') {
            $this->initial_payment_amount = $this->formatCents($this->totalCents());
            $this->due_date = '';
            $this->auto_cash_filled = true;

            if ($this->guided_mode) {
                $this->guided_step = self::GUIDED_STEP_MAX;
            }
            return;
        }

        if ($this->auto_cash_filled) {
            $this->initial_payment_amount = '';
        }

        $this->auto_cash_filled = false;

        if ($this->guided_mode) {
            $this->guided_step = max($this->guided_step, 5);
        }
    }

    public function updatedItems(): void
    {
        if ($this->payment_type === 'cash' && $this->auto_cash_filled) {
            $this->initial_payment_amount = $this->formatCents($this->totalCents());
        }

        if (! $this->payment_type_locked) {
            $this->inferPaymentType();
        }
    }

    public function updatedInitialPaymentAmount(): void
    {
        if ($this->payment_type === 'cash' && $this->auto_cash_filled) {
            $this->auto_cash_filled = false;
        }

        if (! $this->payment_type_locked) {
            $this->inferPaymentType();
        }
    }

    public function toggleGuidedMode(): void
    {
        $this->guided_mode = ! $this->guided_mode;
        $this->guided_step = self::GUIDED_STEP_MIN;
        $this->resetErrorBag();
    }

    public function guidedNext(): void
    {
        if (! $this->guided_mode) {
            return;
        }

        if ($this->guided_step >= self::GUIDED_STEP_MAX) {
            return;
        }

        if (! $this->validateGuidedStep($this->guided_step)) {
            return;
        }

        if ($this->guided_step === 4) {
            if ($this->payment_type === 'cash') {
                $this->setPaymentType('cash');
                $this->guided_step = self::GUIDED_STEP_MAX;
                return;
            }

            if ($this->payment_type === 'credit') {
                $this->guided_step = 5;
                return;
            }
        }

        $this->guided_step = min(self::GUIDED_STEP_MAX, $this->guided_step + 1);
    }

    public function guidedBack(): void
    {
        if (! $this->guided_mode) {
            return;
        }

        if ($this->guided_step <= self::GUIDED_STEP_MIN) {
            return;
        }

        if ($this->guided_step === self::GUIDED_STEP_MAX && $this->payment_type === 'cash') {
            $this->guided_step = 4;
            return;
        }

        $this->guided_step = max(self::GUIDED_STEP_MIN, $this->guided_step - 1);
    }

    public function lineTotalCents(int $index): int
    {
        $item = $this->items[$index] ?? null;

        if (! is_array($item)) {
            return 0;
        }

        $quantity = max(0, (int) ($item['quantity'] ?? 0));
        $unitPriceCents = max(0, $this->moneyToCents($item['unit_price'] ?? 0));

        return $quantity * $unitPriceCents;
    }

    public function totalCents(): int
    {
        $total = 0;

        foreach (array_keys($this->items) as $index) {
            $total += $this->lineTotalCents((int) $index);
        }

        return $total;
    }

    public function paidCents(): int
    {
        return max(0, $this->moneyToCents($this->initial_payment_amount));
    }

    public function balanceCents(): int
    {
        return max($this->totalCents() - $this->paidCents(), 0);
    }

    public function dueDateRequired(): bool
    {
        return $this->paidCents() > 0 && $this->balanceCents() > 0;
    }

    private function validateGuidedStep(int $step): bool
    {
        $this->resetErrorBag();

        if ($step === 1) {
            if (trim($this->customer_name) === '') {
                $this->addError('customer_name', 'El nombre del cliente es obligatorio.');
                return false;
            }

            return true;
        }

        if ($step === 3) {
            if (count($this->items) < 1) {
                $this->addError('items', 'Debe agregar al menos un producto.');
                return false;
            }

            $seenProducts = [];

            foreach ($this->items as $index => $item) {
                if (! is_array($item)) {
                    $this->addError('items', 'Debe agregar al menos un producto.');
                    return false;
                }

                $productId = trim((string) ($item['product_id'] ?? ''));
                if ($productId === '') {
                    $this->addError("items.$index.product_id", 'Seleccione un producto.');
                    return false;
                }

                if (in_array($productId, $seenProducts, true)) {
                    $this->addError("items.$index.product_id", 'No repita el mismo producto.');
                    return false;
                }

                $seenProducts[] = $productId;

                $quantity = (int) ($item['quantity'] ?? 0);
                if ($quantity < 1) {
                    $this->addError("items.$index.quantity", 'La cantidad debe ser al menos 1.');
                    return false;
                }

                $unitPriceRaw = trim((string) ($item['unit_price'] ?? ''));
                if ($unitPriceRaw === '') {
                    $this->addError("items.$index.unit_price", 'Ingrese un precio.');
                    return false;
                }
            }

            if ($this->totalCents() <= 0) {
                $this->addError('items', 'La venta debe tener un total mayor a 0.');
                return false;
            }

            return true;
        }

        if ($step === 4) {
            if (! in_array($this->payment_type, ['cash', 'credit'], true)) {
                $this->addError('payment_type', 'Seleccione una opción.');
                return false;
            }

            return true;
        }

        if ($step === 5) {
            $raw = trim($this->initial_payment_amount);
            if ($raw === '') {
                return true;
            }

            if (! preg_match('/^\d+(?:[\.,]\d{1,2})?$/', $raw)) {
                $this->addError('initial_payment_amount', 'Ingrese un monto válido (máximo 2 decimales).');
                return false;
            }

            if ($this->paidCents() > $this->totalCents()) {
                $this->addError('initial_payment_amount', 'El pago inicial no puede ser mayor al total.');
                return false;
            }

            return true;
        }

        if ($step === 6) {
            $due = trim($this->due_date);

            if ($this->dueDateRequired() && $due === '') {
                $this->addError('due_date', 'Ingrese una fecha prometida si queda saldo pendiente y hubo un pago inicial.');
                return false;
            }

            if ($due !== '' && $due < now()->toDateString()) {
                $this->addError('due_date', 'La fecha prometida no puede ser anterior a hoy.');
                return false;
            }

            return true;
        }

        return true;
    }

    private function firstErrorStepFromSession(): ?int
    {
        $errors = session('errors');
        if (! $errors || ! method_exists($errors, 'getBag')) {
            return null;
        }

        $bag = $errors->getBag('default');
        if (! method_exists($bag, 'messages')) {
            return null;
        }

        $messages = $bag->messages();
        if (! is_array($messages) || $messages === []) {
            return null;
        }

        $minStep = null;

        foreach (array_keys($messages) as $key) {
            $candidate = null;

            if ($key === 'customer_name') {
                $candidate = 1;
            } elseif ($key === 'delivery_location') {
                $candidate = 2;
            } elseif ($key === 'items' || str_starts_with($key, 'items.')) {
                $candidate = 3;
            } elseif ($key === 'initial_payment_amount') {
                $candidate = 5;
            } elseif ($key === 'due_date') {
                $candidate = 6;
            }

            if ($candidate === null) {
                continue;
            }

            $minStep = $minStep === null ? $candidate : min($minStep, $candidate);
        }

        if ($minStep === null) {
            return null;
        }

        return max(self::GUIDED_STEP_MIN, min(self::GUIDED_STEP_MAX, $minStep));
    }

    private function inferPaymentType(): void
    {
        if ($this->payment_type_locked) {
            return;
        }

        $totalCents = $this->totalCents();
        $paidCents = $this->paidCents();

        if ($totalCents > 0 && abs($paidCents - $totalCents) <= 1) {
            $this->payment_type = 'cash';
            return;
        }

        if ($paidCents > 0 || trim($this->due_date) !== '') {
            $this->payment_type = 'credit';
            return;
        }

        $this->payment_type = null;
    }

    private function moneyToCents(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = str_replace(',', '.', (string) $value);

        if (! str_contains($normalized, '.')) {
            return ((int) $normalized) * 100;
        }

        [$whole, $decimals] = explode('.', $normalized, 2);
        $wholePart = (int) $whole;
        $decimalPart = (int) str_pad(substr($decimals, 0, 2), 2, '0');

        return ($wholePart * 100) + $decimalPart;
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
};
?>

<div>
    @php
        $formatCents = fn (int $cents) => number_format($cents / 100, 2, '.', '');
        $totalCents = $this->totalCents();
        $paidCents = $this->paidCents();
        $balanceCents = $this->balanceCents();
    @endphp

    <div class="mt-5 flex justify-end">
        <button type="button" wire:click="toggleGuidedMode"
            class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-muted px-5 py-3 text-lg font-bold text-foreground transition hover:bg-border">
            <span class="text-2xl">{{ $guided_mode ? '↩️' : '🧭' }}</span>
            <span>{{ $guided_mode ? 'Modo normal' : 'Modo guiado' }}</span>
        </button>
    </div>

    <form method="POST" action="{{ route('sales.store') }}" class="mt-6 space-y-6" novalidate>
        @csrf

        <input type="hidden" name="_guided_mode" value="{{ $guided_mode ? '1' : '0' }}">
        <input type="hidden" name="_guided_step" value="{{ $guided_step }}">
        <input type="hidden" name="payment_type" value="{{ $payment_type ?? '' }}">

        <div class="grid grid-cols-1 gap-5 {{ $guided_mode ? '' : 'md:grid-cols-2' }}">
            <div @if($guided_mode && $guided_step !== 1) hidden @endif>
                <label for="customer_name" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                    <span class="text-2xl">👤</span>
                    <span>Nombre del cliente</span>
                </label>
                <input type="text" name="customer_name" id="customer_name" required wire:model.defer="customer_name"
                    placeholder="Ejemplo: María Gómez"
                    class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                @error('customer_name')
                    <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                @enderror
            </div>

            <div @if($guided_mode && $guided_step !== 2) hidden @endif>
                <label for="delivery_location" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                    <span class="text-2xl">📍</span>
                    <span>Lugar de entrega <span class="font-normal text-foreground-muted">(opcional)</span></span>
                </label>
                <input type="text" name="delivery_location" id="delivery_location" wire:model.defer="delivery_location"
                    placeholder="Ejemplo: Depósito Central"
                    class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                @error('delivery_location')
                    <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="rounded-3xl border border-border bg-muted p-4 sm:p-6" @if($guided_mode && $guided_step !== 3) hidden @endif>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">Productos</h3>
                    <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                        Agregue los productos incluidos en la venta.
                    </p>
                </div>

                <button type="button" wire:click="addItem"
                    class="inline-flex min-h-[56px] items-center justify-center gap-3 rounded-2xl bg-success px-5 py-3 text-lg font-bold text-success-foreground transition hover:bg-success-hover">
                    <span class="text-2xl">➕</span>
                    <span>Agregar producto</span>
                </button>
            </div>

            @if ($show_item_added_notice)
                <div wire:poll.3s="clearItemAddedNotice"
                    class="mt-4 rounded-2xl border border-success-border bg-success-soft px-4 py-3 text-lg font-medium text-success sm:text-xl">
                    ✅ Se agregó un nuevo producto para llenar.
                </div>
            @endif

            @if (count($items) === 0)
                <div class="mt-6 rounded-2xl border border-border bg-surface p-4 text-lg text-foreground-muted sm:text-xl">
                    No hay productos agregados. Use <span class="font-bold text-foreground">“Agregar producto”</span> para comenzar.
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach ($items as $index => $item)
                        @php
                            $productSelected = trim((string) ($item['product_id'] ?? '')) !== '';
                            $quantityValue = (int) ($item['quantity'] ?? 0);
                            $unitPriceRaw = trim((string) ($item['unit_price'] ?? ''));
                            $unitPriceCents = $this->moneyToCents($unitPriceRaw);
                            $itemCompleted = $productSelected && $quantityValue >= 1 && $unitPriceRaw !== '' && $unitPriceCents > 0;
                        @endphp

                        <div
                            wire:key="sale-item-{{ $item['key'] ?? $index }}"
                            class="rounded-2xl border p-4 shadow-sm {{ $itemCompleted ? 'border-success-border bg-success-soft' : 'border-border bg-surface' }}"
                        >
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                <div class="md:col-span-5">
                                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧴 Producto</label>
                                    <select name="items[{{ $index }}][product_id]" required
                                        wire:model.live="items.{{ $index }}.product_id"
                                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                        <option value="">Seleccione un producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.product_id")
                                        <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🔢 Cantidad</label>
                                    <input type="number" name="items[{{ $index }}][quantity]" min="1" required
                                        wire:model.live.debounce.250ms="items.{{ $index }}.quantity"
                                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                    @error("items.$index.quantity")
                                        <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="md:col-span-3">
                                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💵 Precio</label>
                                    <input type="number" name="items[{{ $index }}][unit_price]" min="0" step="0.01" required
                                        wire:model.live.debounce.250ms="items.{{ $index }}.unit_price"
                                        class="min-h-[56px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20">
                                    @error("items.$index.unit_price")
                                        <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧮 Subtotal</label>
                                    <div class="flex min-h-[56px] items-center rounded-2xl border border-border bg-muted px-4 text-lg font-bold text-foreground">
                                        {{ $formatCents($this->lineTotalCents($index)) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-2xl bg-danger px-5 py-3 text-lg font-bold text-danger-foreground transition hover:bg-danger-hover">
                                    <span class="text-2xl">🗑️</span>
                                    <span>Quitar</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @error('items')
                <div class="mt-4 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
            @enderror
        </div>

        <div class="rounded-3xl border border-border bg-muted p-4 sm:p-6" @if($guided_mode && $guided_step < 4) hidden @endif>
            <h3 class="text-2xl font-extrabold text-foreground sm:text-3xl">Pago</h3>
            <p class="mt-1 text-lg text-foreground-muted sm:text-xl">
                Si hubo un pago inicial y queda saldo pendiente, ingrese una fecha prometida.
            </p>

            <div class="mt-5 rounded-2xl border border-border bg-surface p-4" @if($guided_mode && $guided_step !== 4) hidden @endif>
                <p class="text-xl font-extrabold text-foreground sm:text-2xl">¿El pago fue al contado o a crédito?</p>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="setPaymentType('cash')" aria-pressed="{{ $payment_type === 'cash' ? 'true' : 'false' }}"
                        class="inline-flex min-h-[60px] items-center justify-center gap-3 rounded-2xl border-2 px-6 py-4 text-xl font-bold transition {{ $payment_type === 'cash' ? 'border-success-border bg-success text-success-foreground hover:bg-success-hover' : 'border-border-strong bg-muted text-foreground hover:bg-border' }}">
                        <span class="text-2xl">💵</span>
                        <span>Al contado</span>
                    </button>

                    <button type="button" wire:click="setPaymentType('credit')" aria-pressed="{{ $payment_type === 'credit' ? 'true' : 'false' }}"
                        class="inline-flex min-h-[60px] items-center justify-center gap-3 rounded-2xl border-2 px-6 py-4 text-xl font-bold transition {{ $payment_type === 'credit' ? 'border-border-strong bg-primary text-primary-foreground hover:bg-primary-hover' : 'border-border-strong bg-muted text-foreground hover:bg-border' }}">
                        <span class="text-2xl">📌</span>
                        <span>A crédito</span>
                    </button>
                </div>

                @error('payment_type')
                    <div class="mt-3 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                @enderror
            </div>

            @if ($payment_type !== 'cash')
                <div class="mt-5 grid grid-cols-1 gap-5 {{ $guided_mode ? '' : 'md:grid-cols-2' }}" @if($guided_mode && !in_array($guided_step, [5, 6], true)) hidden @endif>
                    <div @if($guided_mode && $guided_step !== 5) hidden @endif>
                        <label for="initial_payment_amount" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                            <span class="text-2xl">💳</span>
                            <span>Pago inicial <span class="font-normal text-foreground-muted">(opcional)</span></span>
                        </label>
                        <input type="number" name="initial_payment_amount" id="initial_payment_amount" min="0" step="0.01"
                            wire:model.live.debounce.250ms="initial_payment_amount" placeholder="Ejemplo: 100"
                            class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                        @error('initial_payment_amount')
                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div @if($guided_mode && $guided_step !== 6) hidden @endif>
                        <label for="due_date" class="mb-3 flex items-center gap-2 text-xl font-bold text-foreground sm:text-2xl">
                            <span class="text-2xl">📅</span>
                            <span>Fecha prometida <span class="font-normal text-foreground-muted">(si queda saldo y hubo pago)</span></span>
                        </label>
                        <input type="date" name="due_date" id="due_date" min="{{ now()->toDateString() }}"
                            wire:model.defer="due_date" @required($this->dueDateRequired())
                            class="min-h-[64px] w-full rounded-2xl border-2 border-border-strong bg-surface px-4 py-3 text-lg text-foreground shadow-sm outline-none transition focus:border-success focus:ring-4 focus:ring-success/20 sm:text-xl">
                        @error('due_date')
                            <div class="mt-2 text-lg font-medium text-danger" data-validation-error>⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="mt-5 rounded-2xl border border-border bg-surface p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">🧾 Total</span>
                        <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($totalCents) }}</span>
                    </div>
                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">💳 Pagado</span>
                        <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($paidCents) }}</span>
                    </div>
                    <div>
                        <span class="mb-1 block text-sm font-bold uppercase tracking-wide text-foreground-muted">⏳ Saldo</span>
                        <span class="text-2xl font-extrabold text-foreground">{{ $formatCents($balanceCents) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($guided_mode)
            <div class="mt-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="guidedBack" @disabled($guided_step <= 1)
                        class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-muted px-6 py-4 text-xl font-bold text-foreground transition hover:bg-border disabled:opacity-60">
                        <span class="text-2xl">⬅️</span>
                        <span>Atrás</span>
                    </button>

                    <button type="button" wire:click="guidedNext" @if($guided_step >= 7) hidden @endif
                        class="inline-flex min-h-[60px] w-full items-center justify-center gap-3 rounded-2xl bg-primary px-6 py-4 text-xl font-bold text-primary-foreground transition hover:bg-primary-hover">
                        <span>Siguiente</span>
                        <span class="text-2xl">➡️</span>
                    </button>
                </div>
            </div>
        @endif

        <button type="submit"
            @if($guided_mode && $guided_step !== 7) hidden @endif
            class="inline-flex min-h-[64px] w-full items-center justify-center gap-3 rounded-2xl bg-success px-6 py-4 text-xl font-bold text-success-foreground transition hover:bg-success-hover active:scale-[0.99] sm:text-2xl">
            <span class="text-2xl">✅</span>
            <span>Guardar venta</span>
        </button>
    </form>
</div>
