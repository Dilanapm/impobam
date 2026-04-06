<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de ventas</title>
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

        .muted {
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        $formatCents = fn (int $cents) => number_format($cents / 100, 2, '.', '');
    @endphp

    <h1>Reporte de ventas</h1>
    <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
    <div class="meta">Periodo: {{ $range['label'] }} ({{ $range['start']->format('d/m/Y') }} - {{ $range['end']->format('d/m/Y') }})</div>

    <div class="box">
        <table class="summary-table">
            <tr>
                <td><strong>Total de ventas</strong></td>
                <td class="right">{{ $summary['count'] }}</td>
                <td><strong>Contado</strong></td>
                <td class="right">{{ $summary['cashCount'] }}</td>
                <td><strong>Crédito</strong></td>
                <td class="right">{{ $summary['creditCount'] }}</td>
            </tr>
            <tr>
                <td><strong>Total vendido</strong></td>
                <td class="right">{{ $formatCents($summary['soldCents']) }}</td>
                <td><strong>Total cobrado</strong></td>
                <td class="right">{{ $formatCents($summary['paidCents']) }}</td>
                <td><strong>Saldo pendiente</strong></td>
                <td class="right">{{ $formatCents($summary['balanceCents']) }}</td>
            </tr>
        </table>
        <div class="muted">Nota: montos en 2 decimales.</div>
    </div>

    <table class="data-table">
        <colgroup>
            <col style="width: 12%">
            <col style="width: 7%">
            <col style="width: 18%">
            <col style="width: 16%">
            <col style="width: 10%">
            <col style="width: 10%">
            <col style="width: 10%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="center">Venta</th>
                <th>Cliente</th>
                <th>Lugar</th>
                <th class="right">Total</th>
                <th class="right">Pagado</th>
                <th class="right">Saldo</th>
                <th class="center">Promesa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    /** @var \App\Models\Sale $sale */
                    $sale = $row['sale'];
                @endphp
                <tr>
                    <td>{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="center">#{{ $sale->id }}</td>
                    <td>{{ $sale->customer_name }}</td>
                    <td>{{ $sale->delivery_location ?? '—' }}</td>
                    <td class="right">{{ $formatCents($row['totalCents']) }}</td>
                    <td class="right">{{ $formatCents($row['paidCents']) }}</td>
                    <td class="right">{{ $formatCents($row['balanceCents']) }}</td>
                    <td class="center">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No hay ventas para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
