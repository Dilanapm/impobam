<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 16px;
            color: #1f2937;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        }

        .topbar h1 {
            margin: 0 0 6px 0;
            color: #1f2937;
            font-size: 28px;
        }

        .topbar p {
            margin: 0;
        }

        .logout-form {
            margin: 0;
            min-width: 170px;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }

        .status-message {
            background: #e8fff1;
            color: #166534;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        }

        .card h3 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #6b7280;
            line-height: 1.4;
        }

        .card .value {
            font-size: 30px;
            font-weight: bold;
            color: #111827;
            word-break: break-word;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
            margin-bottom: 24px;
        }

        .panel h2 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 22px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: end;
        }

        .field {
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #374151;
            font-size: 14px;
        }

        input,
        select,
        button,
        a {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .secondary-link {
            display: inline-block;
            text-align: center;
            background: #f3f4f6;
            color: #111827;
            text-decoration: none;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
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
            min-width: 860px;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
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
        }

        .pagination {
            margin-top: 18px;
            overflow-x: auto;
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

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 18px 0;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .topbar {
                flex-direction: column;
                align-items: stretch;
                padding: 16px;
            }

            .topbar h1 {
                font-size: 24px;
            }

            .logout-form {
                width: 100%;
                min-width: auto;
            }

            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .card .value {
                font-size: 24px;
            }

            .panel {
                padding: 16px;
            }

            .panel h2 {
                font-size: 20px;
            }

            .filters {
                grid-template-columns: 1fr;
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
            .cards {
                grid-template-columns: 1fr;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .panel h2 {
                font-size: 18px;
            }

            input,
            select,
            button,
            a {
                font-size: 16px;
                min-height: 44px;
            }

            .delete-btn,
            .logout-btn {
                min-height: 44px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="topbar">
            <div>
                <h1>Panel del administrador</h1>
                <p class="muted">Control de salidas registradas por los empleados.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>

        @if (session('status'))
            <div class="status-message">
                {{ session('status') }}
            </div>
        @endif

        <div class="cards">
            <div class="card">
                <h3>Total de registros</h3>
                <div class="value">{{ $totalRecords }}</div>
            </div>

            <div class="card">
                <h3>Total de unidades retiradas</h3>
                <div class="value">{{ $totalUnits }}</div>
            </div>

            <div class="card">
                <h3>Registros de hoy</h3>
                <div class="value">{{ $todayRecords }}</div>
            </div>

            <div class="card">
                <h3>Unidades retiradas hoy</h3>
                <div class="value">{{ $todayUnits }}</div>
            </div>
        </div>

        <div class="panel">
            <h2>Filtros</h2>

            <form method="GET" action="{{ route('dashboard') }}">
                <div class="filters">
                    <div class="field">
                        <label for="product_id">Producto</label>
                        <select name="product_id" id="product_id">
                            <option value="">Todos</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="start_date">Desde</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    </div>

                    <div class="field">
                        <label for="end_date">Hasta</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <div class="field">
                        <button type="submit">Filtrar</button>
                    </div>

                    <div class="field">
                        <a href="{{ route('dashboard') }}" class="secondary-link">Limpiar filtros</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2>Historial de salidas</h2>

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
                        @forelse($outputs as $output)
                            <tr>
                                <td>{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                                <td>{{ $output->quantity }}</td>
                                <td>{{ $output->employee_name ?: '—' }}</td>
                                <td>{{ $output->notes ?: '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.stock-outputs.destroy', $output) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-btn"
                                            onclick="return confirm('¿Eliminar este registro?')">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">No hay registros para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-records">
                @forelse($outputs as $output)
                    <div class="record-card">
                        <div class="record-row">
                            <span class="record-label">Fecha</span>
                            <span class="record-value">{{ $output->moved_at?->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Producto</span>
                            <span class="record-value">{{ $output->product?->name ?? 'Producto no disponible' }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Cantidad</span>
                            <span class="record-value">{{ $output->quantity }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Empleado</span>
                            <span class="record-value">{{ $output->employee_name ?: '—' }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Observación</span>
                            <span class="record-value">{{ $output->notes ?: '—' }}</span>
                        </div>

                        <div class="record-action">
                            <form method="POST" action="{{ route('admin.stock-outputs.destroy', $output) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn"
                                    onclick="return confirm('¿Eliminar este registro?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="record-card">
                        <div class="record-value">No hay registros para mostrar.</div>
                    </div>
                @endforelse
            </div>

            <div class="pagination">
                {{ $outputs->links() }}
            </div>
        </div>
    </div>
</body>

</html>