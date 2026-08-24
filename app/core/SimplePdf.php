<?php

namespace App\Core;

// Generador mínimo de PDF sin dependencias externas (apto para hosting compartido).
// Soporta texto, líneas y rectángulos sobre páginas A4 usando fuentes core Helvetica.
class SimplePdf
{
    private array $content = [];
    private array $pages = [];
    private float $width = 595.28;   // A4 (puntos)
    private float $height = 841.89;
    private float $marginLeft = 48;
    private float $marginRight = 48;
    private float $marginTop = 56;
    private float $marginBottom = 56;

    public function __construct()
    {
        $this->newPage();
    }

    public function width(): float
    {
        return $this->width;
    }

    public function height(): float
    {
        return $this->height;
    }

    public function marginLeft(): float
    {
        return $this->marginLeft;
    }

    public function marginRight(): float
    {
        return $this->marginRight;
    }

    public function marginTop(): float
    {
        return $this->marginTop;
    }

    public function marginBottom(): float
    {
        return $this->marginBottom;
    }

    public function newPage(): void
    {
        if ($this->content) {
            $this->pages[] = implode("\n", $this->content);
        }
        $this->content = [];
    }

    public function text(string $txt, float $x, float $y, float $size = 10, string $font = 'F1', float $gray = 0): void
    {
        if ($txt === '') {
            return;
        }
        $this->content[] = sprintf(
            '%.3F g BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET',
            $gray,
            $font,
            $size,
            $x,
            $y,
            $this->encode($txt)
        );
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.5, float $gray = 0.7): void
    {
        $this->content[] = sprintf(
            '%.2F w %.3F g %.2F %.2F m %.2F %.2F l S',
            $width,
            $gray,
            $x1,
            $y1,
            $x2,
            $y2
        );
    }

    public function rect(float $x, float $y, float $w, float $h, float $gray): void
    {
        $this->content[] = sprintf('%.3F g %.2F %.2F %.2F %.2F re f', $gray, $x, $y, $w, $h);
    }

    private function encode(string $s): string
    {
        $s = mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
        $s = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        return $s;
    }

    public function output(): string
    {
        if ($this->content) {
            $this->pages[] = implode("\n", $this->content);
        }
        if (!$this->pages) {
            $this->pages[] = '';
        }
        $n = count($this->pages);

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        for ($i = 0; $i < $n; $i++) {
            $kids[] = (6 + 2 * $i) . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        for ($i = 0; $i < $n; $i++) {
            $contentObj = 5 + 2 * $i;
            $pageObj = 6 + 2 * $i;
            $stream = $this->pages[$i];
            $objects[$contentObj] = '<< /Length ' . strlen($stream) . ' >>' . "\n" . 'stream' . "\n" . $stream . "\n" . 'endstream';
            $objects[$pageObj] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->width . ' ' . $this->height . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentObj . ' 0 R >>';
        }

        $max = 6 + 2 * ($n - 1);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= $max; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($max + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($max + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        return $pdf;
    }
}
