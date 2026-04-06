<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo de pago</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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

        .section {
            margin-top: 12px;
        }

        .box {
            border: 1px solid #000;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 2px 6px;
            vertical-align: top;
        }

        .right {
            text-align: right;
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

    <div class="company">
        <div>Empresa unipersonal</div>
        <div class="company-name">IMPORTADOR BAM</div>
        <div>De: Brian Apolaca Marino Telefono: 73066403</div>
        <div><strong>CASA MATRIZ</strong></div>
        <div>Calle Victoria Nro 1753</div>
        <div>El Alto - Bolivia</div>
        <div>NIT: 8341114016</div>
    </div>

    <h1>Recibo de pago</h1>
    <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>

    <div class="section box">
        <table class="kv">
            <tr>
                <td><strong>Venta</strong></td>
                <td>#{{ $sale->id }}</td>
                <td><strong>Cliente</strong></td>
                <td>{{ $sale->customer_name }}</td>
            </tr>
            <tr>
                <td><strong>Pago</strong></td>
                <td>#{{ $payment->id }}</td>
                <td><strong>Fecha de pago</strong></td>
                <td>{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Monto pagado</strong></td>
                <td class="right">{{ $payment->amount }}</td>
                <td><strong>Próxima promesa</strong></td>
                <td>{{ $sale->due_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section box">
        <table class="kv">
            <tr>
                <td><strong>Total venta</strong></td>
                <td class="right">{{ $formatCents($totalCents) }}</td>
            </tr>
            <tr>
                <td><strong>Total pagado</strong></td>
                <td class="right">{{ $formatCents($paidCents) }}</td>
            </tr>
            <tr>
                <td><strong>Saldo</strong></td>
                <td class="right">{{ $formatCents($balanceCents) }}</td>
            </tr>
        </table>
        <div class="muted">Recibo generado desde el sistema.</div>
    </div>
</body>

</html>
