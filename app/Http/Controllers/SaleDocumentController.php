<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class SaleDocumentController extends Controller
{
    public function saleNote(Sale $sale): Response
    {
        [$sale, $totalCents, $paidCents, $balanceCents, $generatedAt] = $this->loadSaleTotals($sale);

        $pdf = Pdf::loadView('sales.documents.sale-note', [
            'sale' => $sale,
            'totalCents' => $totalCents,
            'paidCents' => $paidCents,
            'balanceCents' => $balanceCents,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('nota-venta-' . $sale->id . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    public function saleNoteImage(Sale $sale): Response
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng')) {
            return $this->saleNote($sale);
        }

        [$sale, $totalCents, $paidCents, $balanceCents, $generatedAt] = $this->loadSaleTotals($sale);

        $items = $sale->items->map(function ($item) {
            return [
                'product' => $item->product?->name ?? 'Producto no disponible',
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'line_total' => (string) $item->line_total,
            ];
        })->all();

        try {
            $image = $this->buildSaleNoteImage($sale, $items, $totalCents, $paidCents, $balanceCents, $generatedAt);

            ob_start();
            imagepng($image);
            $png = ob_get_clean();
            imagedestroy($image);
        } catch (Throwable) {
            return $this->saleNote($sale);
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="nota-venta-' . $sale->id . '-' . $generatedAt->format('Y-m-d') . '.png"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function paymentReceipt(Sale $sale, SalePayment $payment): Response
    {
        if ((int) $payment->sale_id !== (int) $sale->id) {
            abort(404);
        }

        [$sale, $payment, $totalCents, $paidCents, $balanceCents, $generatedAt] = $this->loadPaymentReceiptTotals($sale, $payment);

        $pdf = Pdf::loadView('sales.documents.payment-receipt', [
            'sale' => $sale,
            'payment' => $payment,
            'totalCents' => $totalCents,
            'paidCents' => $paidCents,
            'balanceCents' => $balanceCents,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('recibo-pago-venta-' . $sale->id . '-pago-' . $payment->id . '-' . $generatedAt->format('Y-m-d') . '.pdf');
    }

    public function paymentReceiptImage(Sale $sale, SalePayment $payment): Response
    {
        if ((int) $payment->sale_id !== (int) $sale->id) {
            abort(404);
        }

        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng')) {
            return $this->paymentReceipt($sale, $payment);
        }

        [$sale, $payment, $totalCents, $paidCents, $balanceCents, $generatedAt] = $this->loadPaymentReceiptTotals($sale, $payment);

        try {
            $image = $this->buildPaymentReceiptImage($sale, $payment, $totalCents, $paidCents, $balanceCents, $generatedAt);

            ob_start();
            imagepng($image);
            $png = ob_get_clean();
            imagedestroy($image);
        } catch (Throwable) {
            return $this->paymentReceipt($sale, $payment);
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="recibo-pago-venta-' . $sale->id . '-pago-' . $payment->id . '-' . $generatedAt->format('Y-m-d') . '.png"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * @return array{0: Sale, 1: int, 2: int, 3: int, 4: \Illuminate\Support\Carbon}
     */
    private function loadSaleTotals(Sale $sale): array
    {
        $sale->load([
            'items.product',
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $totalCents = $this->moneyToCents($sale->total_amount);
        $paidCents = 0;

        foreach ($sale->payments as $payment) {
            $paidCents += $this->moneyToCents($payment->amount);
        }

        $balanceCents = max($totalCents - $paidCents, 0);
        $generatedAt = now();

        return [$sale, $totalCents, $paidCents, $balanceCents, $generatedAt];
    }

    /**
     * @param array<int, array{product:string, quantity:string, unit_price:string, line_total:string}> $items
     */
    private function buildSaleNoteImage(Sale $sale, array $items, int $totalCents, int $paidCents, int $balanceCents, $generatedAt)
    {
        $width = 1400;
        $margin = 60;
        $font = $this->resolveFontPath();

        $headerHeight = 190;
        $infoBoxHeight = 285;
        $footerHeight = 90;

        $rowHeights = [];

        foreach ($items as $row) {
            $lineCount = count($this->wrapTextByWidth($row['product'], 12, $font, 560));

            $rowHeights[] = max(56, ($lineCount * 24) + 20);
        }

        $tableHeight = array_sum($rowHeights) + 92;
        $totalBoxHeight = 145;
        $imageHeight = $margin + $headerHeight + 28 + $infoBoxHeight + 28 + 40 + $tableHeight + 28 + $totalBoxHeight + $footerHeight + $margin;

        $image = imagecreatetruecolor($width, $imageHeight);

        $white = imagecolorallocate($image, 255, 255, 255);
        $light = imagecolorallocate($image, 243, 244, 246);
        $border = imagecolorallocate($image, 209, 213, 219);
        $text = imagecolorallocate($image, 31, 41, 55);
        $muted = imagecolorallocate($image, 107, 114, 128);
        $green = imagecolorallocate($image, 22, 163, 74);
        $greenSoft = imagecolorallocate($image, 220, 252, 231);
        $greenDark = imagecolorallocate($image, 20, 83, 45);

        imagefill($image, 0, 0, $white);

        imagefilledrectangle($image, 0, 0, $width, $headerHeight, $green);
        $this->drawText($image, 24, $margin, 44, $white, 'IMPORTADOR BAM', $font, true);
        $this->drawText($image, 15, $margin, 78, $white, 'Empresa unipersonal', $font, true);
        $this->drawText($image, 15, $margin, 104, $white, 'De: Brian Apolaca Marino  Telefono: 73066403', $font, true);
        $this->drawText($image, 15, $margin, 130, $white, 'CASA MATRIZ - Calle Victoria Nro 1753 - El Alto - Bolivia', $font, true);
        $this->drawText($image, 15, $margin, 156, $white, 'NIT: 8341114016', $font, true);

        $sectionY = $margin + $headerHeight;

        imagefilledrectangle($image, $margin, $sectionY, $width - $margin, $sectionY + $infoBoxHeight, $light);
        imagerectangle($image, $margin, $sectionY, $width - $margin, $sectionY + $infoBoxHeight, $border);

        $this->drawText($image, 22, $margin + 20, $sectionY + 36, $text, 'Nota de venta', $font, true);
        $this->drawText($image, 13, $margin + 20, $sectionY + 62, $muted, 'Generado: ' . $generatedAt->format('d/m/Y H:i'), $font, true);

        $leftX = $margin + 20;
        $rightX = $margin + 720;
        $labelY = $sectionY + 106;
        $valueY = $sectionY + 132;
        $secondLabelY = $sectionY + 170;
        $secondValueY = $sectionY + 196;

        $this->drawText($image, 15, $leftX, $labelY, $muted, 'Venta', $font, true);
        $this->drawText($image, 18, $leftX, $valueY, $text, '#' . $sale->id, $font, true);

        $this->drawText($image, 15, $rightX, $labelY, $muted, 'Cliente', $font, true);
        $this->drawText($image, 18, $rightX, $valueY, $text, $sale->customer_name, $font, true);

        $this->drawText($image, 15, $leftX, $secondLabelY, $muted, 'Fecha', $font, true);
        $this->drawText($image, 18, $leftX, $secondValueY, $text, $sale->created_at?->format('d/m/Y H:i') ?? '—', $font, true);

        $this->drawText($image, 15, $rightX, $secondLabelY, $muted, 'Fecha prometida', $font, true);
        $this->drawText($image, 18, $rightX, $secondValueY, $text, $sale->due_date?->format('d/m/Y') ?? '—', $font, true);

        $statusY = $sectionY + 252;
        $this->drawText($image, 15, $leftX, $statusY, $muted, 'Estado', $font, true);
        $this->drawText($image, 18, $leftX, $statusY + 26, $text, $balanceCents > 0 ? 'Con saldo pendiente' : 'Pagada', $font, true);

        $tableY = $sectionY + $infoBoxHeight + 28;

        $this->drawText($image, 22, $margin, $tableY + 18, $text, 'Productos', $font, true);
        $tableTop = $tableY + 34;
        $tableBottom = $tableTop + $tableHeight;

        imagefilledrectangle($image, $margin, $tableTop, $width - $margin, $tableBottom, $white);
        imagerectangle($image, $margin, $tableTop, $width - $margin, $tableBottom, $border);

        $col1 = $margin + 16;
        $col2 = $margin + 700;
        $col3 = $margin + 840;
        $col4 = $margin + 1000;
        $headerRowY = $tableTop + 12;

        imagefilledrectangle($image, $margin + 1, $tableTop + 1, $width - $margin - 1, $tableTop + 40, $greenSoft);
        $this->drawText($image, 15, $col1, $headerRowY + 18, $greenDark, 'Producto', $font, true);
        $this->drawText($image, 15, $col2, $headerRowY + 18, $greenDark, 'Cant.', $font, true);
        $this->drawText($image, 15, $col3, $headerRowY + 18, $greenDark, 'Precio', $font, true);
        $this->drawText($image, 15, $col4, $headerRowY + 18, $greenDark, 'Subtotal', $font, true);

        $currentY = $tableTop + 52;

        foreach ($items as $index => $row) {
            $rowHeight = $rowHeights[$index];

            if ($index % 2 === 1) {
                imagefilledrectangle($image, $margin + 1, $currentY, $width - $margin - 1, $currentY + $rowHeight, imagecolorallocate($image, 249, 250, 251));
            }

            imageline($image, $margin, $currentY + $rowHeight, $width - $margin, $currentY + $rowHeight, $border);

            $productLines = $this->wrapTextByWidth($row['product'], 12, $font, 560);

            foreach ($productLines as $lineIndex => $line) {
                $this->drawText($image, 14, $col1, $currentY + 28 + ($lineIndex * 22), $text, $line, $font, true);
            }

            $this->drawText($image, 14, $col2, $currentY + 28, $text, $row['quantity'], $font, true);
            $this->drawText($image, 14, $col3, $currentY + 28, $text, $row['unit_price'], $font, true);
            $this->drawText($image, 14, $col4, $currentY + 28, $text, $row['line_total'], $font, true);

            $currentY += $rowHeight;
        }

        $totalsY = $tableBottom + 28;
        imagefilledrectangle($image, $margin, $totalsY, $width - $margin, $totalsY + $totalBoxHeight, $light);
        imagerectangle($image, $margin, $totalsY, $width - $margin, $totalsY + $totalBoxHeight, $border);

        $this->drawText($image, 22, $margin + 18, $totalsY + 34, $text, 'Totales', $font, true);

        $this->drawText($image, 15, $margin + 18, $totalsY + 72, $text, 'Total', $font, true);
        $this->drawText($image, 15, $width - $margin - 180, $totalsY + 72, $text, $this->moneyFormat($totalCents), $font, true);

        $this->drawText($image, 15, $margin + 18, $totalsY + 98, $text, 'Pagado', $font, true);
        $this->drawText($image, 15, $width - $margin - 180, $totalsY + 98, $text, $this->moneyFormat($paidCents), $font, true);

        $this->drawText($image, 15, $margin + 18, $totalsY + 124, $text, 'Saldo', $font, true);
        $this->drawText($image, 15, $width - $margin - 180, $totalsY + 124, $text, $this->moneyFormat($balanceCents), $font, true);

        $this->drawText($image, 13, $margin, $imageHeight - 28, $muted, 'Esta nota es informativa.', $font, true);

        return $image;
    }

    private function buildPaymentReceiptImage(Sale $sale, SalePayment $payment, int $totalCents, int $paidCents, int $balanceCents, $generatedAt)
    {
        $width = 1300;
        $height = 980;
        $margin = 58;
        $font = $this->resolveFontPath();

        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 147, 197, 253);
        $blueSoft = imagecolorallocate($image, 239, 246, 255);
        $blueMuted = imagecolorallocate($image, 224, 242, 254);
        $border = imagecolorallocate($image, 191, 219, 254);
        $text = imagecolorallocate($image, 30, 41, 59);
        $muted = imagecolorallocate($image, 71, 85, 105);
        $accent = imagecolorallocate($image, 14, 116, 144);

        imagefill($image, 0, 0, $white);

        imagefilledrectangle($image, 0, 0, $width, 190, $blue);
        $this->drawText($image, 28, $margin, 52, $text, 'IMPORTADOR BAM', $font, true);
        $this->drawText($image, 15, $margin, 82, $text, 'Empresa unipersonal', $font, true);
        $this->drawText($image, 15, $margin, 108, $text, 'De: Brian Apolaca Marino  Telefono: 73066403', $font, true);
        $this->drawText($image, 15, $margin, 134, $text, 'CASA MATRIZ - Calle Victoria Nro 1753 - El Alto - Bolivia', $font, true);
        $this->drawText($image, 15, $margin, 160, $text, 'NIT: 8341114016', $font, true);

        $titleY = 230;
        $this->drawText($image, 32, $margin, $titleY, $text, 'Recibo de pago', $font, true);
        $this->drawText($image, 14, $margin, $titleY + 34, $muted, 'Generado: ' . $generatedAt->format('d/m/Y H:i'), $font, true);

        $boxTop = 286;
        $boxBottom = 610;
        imagefilledrectangle($image, $margin, $boxTop, $width - $margin, $boxBottom, $blueSoft);
        imagerectangle($image, $margin, $boxTop, $width - $margin, $boxBottom, $border);

        $leftX = $margin + 26;
        $rightX = $margin + 680;

        $this->drawText($image, 15, $leftX, $boxTop + 46, $muted, 'Venta', $font, true);
        $this->drawText($image, 22, $leftX, $boxTop + 82, $text, '#' . $sale->id, $font, true);

        $this->drawText($image, 15, $rightX, $boxTop + 46, $muted, 'Cliente', $font, true);
        $this->drawText($image, 22, $rightX, $boxTop + 82, $text, $sale->customer_name, $font, true);

        $this->drawText($image, 15, $leftX, $boxTop + 146, $muted, 'Pago', $font, true);
        $this->drawText($image, 22, $leftX, $boxTop + 182, $text, '#' . $payment->id, $font, true);

        $this->drawText($image, 15, $rightX, $boxTop + 146, $muted, 'Fecha de pago', $font, true);
        $this->drawText($image, 22, $rightX, $boxTop + 182, $text, $payment->paid_at?->format('d/m/Y H:i') ?? '—', $font, true);

        $this->drawText($image, 15, $leftX, $boxTop + 246, $muted, 'Monto pagado', $font, true);
        $this->drawText($image, 24, $leftX, $boxTop + 284, $accent, (string) $payment->amount, $font, true);

        $this->drawText($image, 15, $rightX, $boxTop + 246, $muted, 'Proxima promesa', $font, true);
        $this->drawText($image, 22, $rightX, $boxTop + 282, $text, $sale->due_date?->format('d/m/Y') ?? '—', $font, true);

        $totalsTop = 646;
        $totalsBottom = 860;
        imagefilledrectangle($image, $margin, $totalsTop, $width - $margin, $totalsBottom, $blueMuted);
        imagerectangle($image, $margin, $totalsTop, $width - $margin, $totalsBottom, $border);

        $this->drawText($image, 24, $margin + 20, $totalsTop + 40, $text, 'Resumen de saldos', $font, true);

        $rowY = $totalsTop + 88;
        $this->drawText($image, 18, $margin + 24, $rowY, $text, 'Total venta', $font, true);
        $this->drawText($image, 18, $width - $margin - 210, $rowY, $text, $this->moneyFormat($totalCents), $font, true);

        $rowY += 44;
        $this->drawText($image, 18, $margin + 24, $rowY, $text, 'Total pagado', $font, true);
        $this->drawText($image, 18, $width - $margin - 210, $rowY, $text, $this->moneyFormat($paidCents), $font, true);

        $rowY += 44;
        $this->drawText($image, 18, $margin + 24, $rowY, $text, 'Saldo', $font, true);
        $this->drawText($image, 18, $width - $margin - 210, $rowY, $text, $this->moneyFormat($balanceCents), $font, true);

        $this->drawText($image, 14, $margin, $height - 28, $muted, 'Este recibo confirma el registro del pago en sistema.', $font, true);

        return $image;
    }

    /**
     * @return array{0: Sale, 1: SalePayment, 2: int, 3: int, 4: int, 5: \Illuminate\Support\Carbon}
     */
    private function loadPaymentReceiptTotals(Sale $sale, SalePayment $payment): array
    {
        $sale->load([
            'items.product',
            'payments' => fn ($query) => $query->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $totalCents = $this->moneyToCents($sale->total_amount);
        $paidCents = 0;

        foreach ($sale->payments as $row) {
            $paidCents += $this->moneyToCents($row->amount);
        }

        $balanceCents = max($totalCents - $paidCents, 0);
        $generatedAt = now();

        return [$sale, $payment, $totalCents, $paidCents, $balanceCents, $generatedAt];
    }

    /**
     * @return array<int, string>
     */
    private function wrapTextByWidth(string $text, int $fontSize, string $fontFile, int $maxWidth): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if ($this->textWidth($candidate, $fontSize, $fontFile) <= $maxWidth) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines === [] ? [''] : $lines;
    }

    private function textWidth(string $text, int $fontSize, string $fontFile): int
    {
        if ($this->canUseTtf($fontFile)) {
            $box = imagettfbbox($fontSize, 0, $fontFile, $this->encodeForTtf($text));

            return abs($box[2] - $box[0]);
        }

        return max(strlen($text), 1) * 8;
    }

    private function drawText($image, int $fontSize, int $x, int $y, int $color, string $text, string $fontFile, bool $utf8 = false): void
    {
        if ($this->canUseTtf($fontFile)) {
            $text = $utf8 ? $this->encodeForTtf($text) : $text;
            imagettftext($image, $fontSize, 0, $x, $y, $color, $fontFile, $text);
            return;
        }

        $builtinFont = $this->builtinFontForSize($fontSize);
        imagestring($image, $builtinFont, $x, max($y - (imagefontheight($builtinFont) + 2), 0), $text, $color);
    }

    private function encodeForTtf(string $text): string
    {
        if (! function_exists('iconv')) {
            return $text;
        }

        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);

        return $converted !== false ? $converted : $text;
    }

    private function canUseTtf(string $fontFile): bool
    {
        return function_exists('imagettftext')
            && function_exists('imagettfbbox')
            && is_file($fontFile);
    }

    private function resolveFontPath(): string
    {
        $candidates = [
            storage_path('fonts/DejaVuSans.ttf'),
            base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    }

    private function builtinFontForSize(int $fontSize): int
    {
        if ($fontSize >= 20) {
            return 5;
        }

        if ($fontSize >= 16) {
            return 4;
        }

        if ($fontSize >= 13) {
            return 3;
        }

        return 2;
    }

    private function moneyFormat(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
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
}
