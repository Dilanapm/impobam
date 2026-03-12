<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de salida de productos</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 16px;
            color: #1f2937;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        h1,
        h2 {
            margin-top: 0;
            color: #1f2937;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 16px;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #e8fff1;
            color: #166534;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .alert-error {
            color: #b91c1c;
            font-size: 14px;
            margin-top: 6px;
            line-height: 1.4;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #374151;
            font-size: 15px;
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            padding: 14px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 16px;
            box-sizing: border-box;
        }

        input,
        select,
        textarea {
            background: #fff;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            background: #2563eb;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background .2s ease;
        }

        .submit-btn:hover {
            background: #1d4ed8;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background .2s ease;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .divider {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            min-width: 720px;
        }

        table th,
        table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 8px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        table th {
            background: #f9fafb;
            font-weight: bold;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 18px 0;
        }

        .mobile-records {
            display: none;
        }

        .record-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .record-row {
            margin-bottom: 10px;
        }

        .record-row:last-child {
            margin-bottom: 0;
        }

        .record-label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: .3px;
        }

        .record-value {
            font-size: 14px;
            color: #111827;
            line-height: 1.4;
            word-break: break-word;
        }

        .record-action {
            margin-top: 14px;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 18px 14px;
                border-radius: 12px;
            }

            h1 {
                font-size: 24px;
                line-height: 1.2;
            }

            h2 {
                font-size: 20px;
            }

            .muted {
                font-size: 13px;
            }

            input,
            select,
            textarea,
            button {
                font-size: 16px;
            }

            .table-wrapper {
                display: none;
            }

            .mobile-records {
                display: block;
                margin-top: 14px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 16px 12px;
            }

            h1 {
                font-size: 22px;
            }

            h2 {
                font-size: 18px;
            }

            label {
                font-size: 14px;
            }

            .submit-btn,
            .delete-btn {
                min-height: 44px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Registro de salida de productos</h1>
        <p class="muted">Complete el formulario cada vez que retire mercadería.</p>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('stock-outputs.store') }}">
            @csrf

            <div class="field">
                <label for="product_id">Producto</label>
                <select name="product_id" id="product_id" required>
                    <option value="">Seleccione un producto</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <div class="alert-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="quantity">Cantidad retirada</label>
                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    min="1"
                    value="{{ old('quantity') }}"
                    placeholder="Ejemplo: 5"
                    required
                >
                @error('quantity')
                    <div class="alert-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="employee_name">Nombre del empleado (opcional)</label>
                <input
                    type="text"
                    name="employee_name"
                    id="employee_name"
                    value="{{ old('employee_name') }}"
                    placeholder="Ejemplo: Juan Pérez"
                >
                @error('employee_name')
                    <div class="alert-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="notes">Observación (opcional)</label>
                <textarea
                    name="notes"
                    id="notes"
                    placeholder="Detalle adicional del retiro"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="alert-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="submit-btn">Registrar salida</button>
        </form>

        <hr class="divider">

        <h2>Últimos registros</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Empleado</th>
                        <th>Observación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOutputs as $output)
                        <tr>
                            <td>{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $output->product->name }}</td>
                            <td>{{ $output->quantity }}</td>
                            <td>{{ $output->employee_name ?? '—' }}</td>
                            <td>{{ $output->notes ?? '—' }}</td>
                            <td>
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
                                            class="delete-btn"
                                            onclick="return confirm('¿Eliminar este último registro?')"
                                        >
                                            Eliminar
                                        </button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Aún no hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-records">
            @forelse($recentOutputs as $output)
                <div class="record-card">
                    <div class="record-row">
                        <span class="record-label">Fecha</span>
                        <span class="record-value">{{ $output->moved_at?->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="record-row">
                        <span class="record-label">Producto</span>
                        <span class="record-value">{{ $output->product->name }}</span>
                    </div>

                    <div class="record-row">
                        <span class="record-label">Cantidad</span>
                        <span class="record-value">{{ $output->quantity }}</span>
                    </div>

                    <div class="record-row">
                        <span class="record-label">Empleado</span>
                        <span class="record-value">{{ $output->employee_name ?? '—' }}</span>
                    </div>

                    <div class="record-row">
                        <span class="record-label">Observación</span>
                        <span class="record-value">{{ $output->notes ?? '—' }}</span>
                    </div>

                    <div class="record-action">
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
                                    class="delete-btn"
                                    onclick="return confirm('¿Eliminar este último registro?')"
                                >
                                    Eliminar
                                </button>
                            </form>
                        @else
                            <span class="record-value">—</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="record-card">
                    <div class="record-value">Aún no hay registros.</div>
                </div>
            @endforelse
        </div>
    </div>
</body>

</html>