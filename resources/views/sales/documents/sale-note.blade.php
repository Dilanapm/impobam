<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota de venta</title>
    <style>
        @page {
            margin: 28px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            color: #1f2937;
        }

        .sheet {
            border: 1px solid #d1d5db;
            border-radius: 14px;
            overflow: hidden;
        }

        h1 {
            font-size: 24px;
            margin: 0;
            line-height: 1.1;
        }

        .meta {
            font-size: 11px;
            margin-top: 6px;
            color: #6b7280;
        }

        .company {
            text-align: center;
            padding: 22px 18px 18px;
            background: #16a34a;
            color: #fff;
        }

        .company .company-name {
            font-size: 20px;
            font-weight: bold;
            margin: 4px 0 6px;
        }

        .section {
            margin-top: 14px;
        }

        .box {
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 16px 18px;
            background: #f9fafb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 9px 10px;
            vertical-align: top;
        }

        .kv td.label {
            width: 18%;
            color: #374151;
            font-weight: bold;
            white-space: nowrap;
        }

        .kv td.value {
            width: 32%;
            color: #111827;
        }

        .data th,
        .data td {
            border: 1px solid #d1d5db;
            padding: 9px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data th {
            font-weight: bold;
            background: #dcfce7;
            color: #14532d;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .muted {
            font-size: 10px;
            color: #6b7280;
            margin-top: 10px;
        }

        .totals td {
            padding: 7px 10px;
        }

        .totals .amount {
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $formatCents = fn (int $cents) => number_format($cents / 100, 2, '.', '');
    @endphp

    <div class="sheet">
        <div class="company">
            <div>Empresa unipersonal</div>
            <div class="company-name">IMPORTADOR BAM</div>
            <div>De: Brian Apolaca Marino Telefono: 73066403</div>
            <div><strong>CASA MATRIZ</strong></div>
            <div>Calle Victoria Nro 1753</div>
            <div>El Alto - Bolivia</div>
            <div>NIT: 8341114016</div>
        </div>

        <div style="padding: 18px 22px 8px;">
            <h1>Nota de venta</h1>
            <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
        </div>

        <div class="section box" style="margin: 0 22px;">
        <table class="kv">
            <tr>
                <td class="label">Venta</td>
                <td class="value">#{{ $sale->id }}</td>
                <td class="label">Fecha</td>
                <td class="value">{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Cliente</td>
                <td class="value">{{ $sale->customer_name }}</td>
                <td class="label">Lugar</td>
                <td class="value">{{ $sale->delivery_location ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha prometida</td>
                <td class="value">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td class="label">Estado</td>
                <td class="value">{{ $balanceCents > 0 ? 'Con saldo pendiente' : 'Pagada' }}</td>
            </tr>
        </table>
        </div>

        <div class="section" style="padding: 0 22px;">
            <table class="data">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="center">Cant.</th>
                        <th class="right">Precio</th>
                        <th class="right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? 'Producto no disponible' }}</td>
                            <td class="center">{{ $item->quantity }}</td>
                            <td class="right">{{ $item->unit_price }}</td>
                            <td class="right">{{ $item->line_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section box" style="margin: 0 22px 22px; background: #ecfdf5;">
        <table class="kv totals">
            <tr>
                <td class="label">Total</td>
                <td class="right amount">{{ $formatCents($totalCents) }}</td>
            </tr>
            <tr>
                <td class="label">Pagado</td>
                <td class="right amount">{{ $formatCents($paidCents) }}</td>
            </tr>
            <tr>
                <td class="label">Saldo</td>
                <td class="right amount">{{ $formatCents($balanceCents) }}</td>
            </tr>
        </table>
            <div class="muted">Esta nota es informativa.</div>
        </div>
    </div>
</body>

</html>
