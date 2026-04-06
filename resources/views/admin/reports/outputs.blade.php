<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de salidas</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        h1 {
            font-size: 18px;
            margin: 0;
        }

        .meta {
            font-size: 10px;
            margin-top: 2px;
        }

        .company {
            text-align: center;
            margin-bottom: 10px;
        }

        .company .company-name {
            font-size: 14px;
            font-weight: bold;
        }

        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 2px 6px;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data-table th {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="company">
        <div>Empresa unipersonal</div>
        <div class="company-name">IMPORTADOR BAM</div>
        <div>De: Brian Apolaca Marino Telefono: 73066403</div>
        <div><strong>CASA MATRIZ</strong></div>
        <div>Calle Victoria Nro 1753</div>
        <div>El Alto - Bolivia</div>
        <div>NIT: 8341114016</div>
    </div>

    <h1>Reporte de salidas</h1>
    <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
    <div class="meta">Periodo: {{ $range['label'] }} ({{ $range['start']->format('d/m/Y') }} - {{ $range['end']->format('d/m/Y') }})</div>

    <div class="box">
        <table class="summary-table">
            <tr>
                <td><strong>Total de registros</strong></td>
                <td class="right">{{ $summary['count'] }}</td>
                <td><strong>Unidades retiradas</strong></td>
                <td class="right">{{ $summary['units'] }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <colgroup>
            <col style="width: 15%">
            <col style="width: 6%">
            <col style="width: 22%">
            <col style="width: 8%">
            <col style="width: 18%">
            <col style="width: 31%">
        </colgroup>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="center">ID</th>
                <th>Producto</th>
                <th class="right">Cantidad</th>
                <th>Empleado</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($outputs as $output)
                <tr>
                    <td>{{ $output->moved_at?->format('d/m/Y H:i') }}</td>
                    <td class="center">{{ $output->id }}</td>
                    <td>{{ $output->product?->name ?? 'Producto no disponible' }}</td>
                    <td class="right">{{ $output->quantity }}</td>
                    <td>{{ $output->employee_name ?: '—' }}</td>
                    <td>{{ $output->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay salidas para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
