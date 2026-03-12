<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .topbar h1 {
            margin: 0;
            color: #1f2937;
        }

        .logout-form {
            margin: 0;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
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
        }

        .card .value {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
            margin-bottom: 24px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: end;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #374151;
        }

        input,
        select,
        button,
        a {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 14px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f9fafb;
        }

        .muted {
            color: #6b7280;
        }

        .pagination {
            margin-top: 18px;
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
            <div style="background:#e8fff1;color:#166534;padding:12px 14px;border-radius:10px;margin-bottom:18px;">
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
                    <div>
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

                    <div>
                        <label for="start_date">Desde</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    </div>

                    <div>
                        <label for="end_date">Hasta</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <div>
                        <button type="submit">Filtrar</button>
                    </div>

                    <div>
                        <a href="{{ route('dashboard') }}" class="secondary-link">Limpiar filtros</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2>Historial de salidas</h2>

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
                            <td>{{ $output->product->name }}</td>
                            <td>{{ $output->quantity }}</td>
                            <td>{{ $output->employee_name ?: '—' }}</td>
                            <td>{{ $output->notes ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.stock-outputs.destroy', $output) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="background:#dc2626;color:white;border:none;padding:8px 10px;border-radius:8px;cursor:pointer;"
                                        onclick="return confirm('¿Eliminar este registro?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No hay registros para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">
                {{ $outputs->links() }}
            </div>
        </div>
    </div>
</body>

</html>
