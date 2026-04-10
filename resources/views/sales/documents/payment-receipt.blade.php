<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo de pago</title>
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
            border: 1px solid #cfd8e3;
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
            color: #667085;
        }

        .company {
            text-align: center;
            padding: 22px 18px 18px;
            background: #93c5fd;
            color: #0f172a;
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
            border: 1px solid #cfd8e3;
            border-radius: 12px;
            padding: 16px 18px;
            background: #f8fafc;
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
            width: 22%;
            color: #334155;
            font-weight: bold;
            white-space: nowrap;
        }

        .kv td.value {
            width: 28%;
            color: #0f172a;
        }

        .right {
            text-align: right;
        }

        .muted {
            font-size: 10px;
            color: #64748b;
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
            <h1>Recibo de pago</h1>
            <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
        </div>

        <div class="section box" style="margin: 0 22px;">
            <table class="kv">
                <tr>
                    <td class="label">Venta</td>
                    <td class="value">#{{ $sale->id }}</td>
                    <td class="label">Cliente</td>
                    <td class="value">{{ $sale->customer_name }}</td>
                </tr>
                <tr>
                    <td class="label">Pago</td>
                    <td class="value">#{{ $payment->id }}</td>
                    <td class="label">Fecha de pago</td>
                    <td class="value">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Monto pagado</td>
                    <td class="value right">{{ $payment->amount }}</td>
                    <td class="label">Próxima promesa</td>
                    <td class="value">{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section box" style="margin: 0 22px 22px; background: #eff6ff;">
            <table class="kv totals">
                <tr>
                    <td class="label">Total venta</td>
                    <td class="right amount">{{ $formatCents($totalCents) }}</td>
                </tr>
                <tr>
                    <td class="label">Total pagado</td>
                    <td class="right amount">{{ $formatCents($paidCents) }}</td>
                </tr>
                <tr>
                    <td class="label">Saldo</td>
                    <td class="right amount">{{ $formatCents($balanceCents) }}</td>
                </tr>
            </table>
            <div class="muted">Recibo generado desde el sistema.</div>
        </div>
    </div>
</body>

</html>
