<?php

namespace App\Core;

// Arma el PDF de una cotización a partir de la cabecera y sus líneas.
class QuotePdf
{
    public static function render(array $quote): string
    {
        $pdf = new SimplePdf();
        $L = $pdf->marginLeft();
        $right = $pdf->width() - $pdf->marginRight();

        // Franja de cabecera
        $bandTop = $pdf->height();
        $pdf->rect(0, $bandTop - 96, $pdf->width(), 96, 0.97);
        $pdf->text('KAMAQ', $L, $bandTop - 54, 20, 'F2', 0.2);
        $pdf->text('Cotización', $L, $bandTop - 78, 11, 'F2', 0.45);
        self::textRight($pdf, (string) $quote['quote_number'], $right, $bandTop - 54, 12, 'F2', 0.3);
        self::textRight($pdf, 'Fecha: ' . date('d/m/Y', strtotime($quote['created_at'])), $right, $bandTop - 72, 9, 'F1', 0.45);

        $y = $bandTop - 96 - 26;

        // Bloque cliente
        $pdf->text('Cliente', $L, $y, 11, 'F2', 0.25);
        $y -= 15;
        $clientLines = [
            'Empresa: ' . $quote['customer_company'],
            'RUT: ' . ($quote['customer_rut'] ?: '—'),
            'Atención: ' . ($quote['contact_person'] ?: '—'),
            'Dirección: ' . ($quote['customer_address'] ?: '—'),
            'Correo: ' . ($quote['customer_email'] ?: '—'),
            'Teléfono: ' . ($quote['customer_phone'] ?: '—'),
        ];
        foreach ($clientLines as $line) {
            $pdf->text($line, $L, $y, 9);
            $y -= 12;
        }

        // Tabla de ítems
        $y -= 12;
        $colCant = $L;
        $colProd = $L + 34;
        $colNotes = $L + 34 + 160;
        $colUnit = $L + 34 + 160 + 150;
        $colTotal = $right;

        self::drawTableHeader($pdf, $L, $right, $y, $colCant, $colProd, $colNotes, $colUnit, $colTotal);
        $y -= 24;

        $lineH = 14;
        foreach ($quote['items'] as $it) {
            $nameLines = self::wrap((string) $it['product_name'], 30);
            $noteLines = self::wrap((string) ($it['notes'] ?? ''), 26);
            $rows = max(count($nameLines), count($noteLines), 1);
            $rowH = $rows * $lineH + 4;

            if ($y - $rowH < $pdf->marginBottom()) {
                $pdf->newPage();
                $y = $pdf->height() - $pdf->marginTop();
                self::drawTableHeader($pdf, $L, $right, $y, $colCant, $colProd, $colNotes, $colUnit, $colTotal);
                $y -= 24;
            }

            $topY = $y;
            $pdf->text((string) $it['quantity'], $colCant, $topY, 9);
            foreach ($nameLines as $i => $line) {
                $pdf->text($line, $colProd, $topY - ($i * $lineH), 9);
            }
            foreach ($noteLines as $i => $line) {
                $pdf->text($line, $colNotes, $topY - ($i * $lineH), 8, 'F1', 0.4);
            }
            self::textRight($pdf, money((float) $it['unit_price']), $colUnit, $topY, 9);
            self::textRight($pdf, money((float) $it['line_total']), $colTotal, $topY, 9);
            $y = $topY - $rowH;
            $pdf->line($L, $y + 2, $right, $y + 2, 0.4, 0.9);
        }

        // Totales
        $y -= 10;
        self::textRight($pdf, 'Subtotal (neto):', $colTotal - 40, $y, 10);
        self::textRight($pdf, money((float) $quote['subtotal']), $colTotal, $y, 10);
        $y -= 14;
        self::textRight($pdf, 'IVA (' . format_tax_rate($quote['tax_rate']) . '%):', $colTotal - 40, $y, 10);
        self::textRight($pdf, money((float) $quote['tax']), $colTotal, $y, 10);
        $y -= 16;
        self::textRight($pdf, 'Total (bruto):', $colTotal - 40, $y, 11, 'F2');
        self::textRight($pdf, money((float) $quote['total']), $colTotal, $y, 11, 'F2');

        // Indicaciones generales
        if (!empty($quote['notes'])) {
            $y -= 22;
            $pdf->text('Indicaciones generales', $L, $y, 10, 'F2', 0.25);
            $y -= 14;
            foreach (self::wrap((string) $quote['notes'], 95) as $line) {
                if ($y < $pdf->marginBottom()) {
                    $pdf->newPage();
                    $y = $pdf->height() - $pdf->marginTop();
                }
                $pdf->text($line, $L, $y, 9);
                $y -= 12;
            }
        }

        return $pdf->output();
    }

    private static function drawTableHeader(SimplePdf $pdf, float $L, float $right, float $y, float $colCant, float $colProd, float $colNotes, float $colUnit, float $colTotal): void
    {
        $pdf->rect($L, $y - 6, $right - $L, 20, 0.93);
        $pdf->text('Cant.', $colCant, $y, 9, 'F2', 0.3);
        $pdf->text('Producto', $colProd, $y, 9, 'F2', 0.3);
        $pdf->text('Indicaciones', $colNotes, $y, 9, 'F2', 0.3);
        self::textRight($pdf, 'P. Unitario', $colUnit, $y, 9, 'F2', 0.3);
        self::textRight($pdf, 'Total', $colTotal, $y, 9, 'F2', 0.3);
    }

    private static function textRight(SimplePdf $pdf, string $txt, float $right, float $y, float $size = 9, string $font = 'F1', float $gray = 0.2): void
    {
        $w = self::textWidth($txt, $size);
        $pdf->text($txt, $right - $w, $y, $size, $font, $gray);
    }

    private static function textWidth(string $txt, float $size): float
    {
        return mb_strlen($txt) * ($size * 0.5);
    }

    private static function wrap(string $text, int $maxChars): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }
        $lines = [];
        while (mb_strlen($text) > $maxChars) {
            $chunk = mb_substr($text, 0, $maxChars);
            $space = mb_strrpos($chunk, ' ');
            $cut = ($space !== false && $space > 0) ? $space : $maxChars;
            $lines[] = rtrim(mb_substr($text, 0, $cut));
            $text = ltrim(mb_substr($text, $cut));
        }
        if ($text !== '') {
            $lines[] = $text;
        }
        return $lines ?: [''];
    }
}
