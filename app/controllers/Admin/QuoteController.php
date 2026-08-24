<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\QuotePdf;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;

class QuoteController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('admin/quotes/index', [
            'pageTitle' => 'Cotizaciones',
            'quotes' => Quote::all('created_at DESC'),
        ], 'admin');
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('admin/quotes/form', [
            'pageTitle' => 'Nueva cotización',
            'quote' => null,
            'products' => Product::forSelect(),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/cotizaciones');
        }

        $header = $this->headerFromRequest();
        if ($header['customer_company'] === '') {
            remember_old($_POST);
            flash('error', 'El nombre de la empresa es obligatorio.');
            redirect('/admin/cotizaciones/crear');
        }

        $items = $this->itemsFromRequest();
        if (!$items) {
            remember_old($_POST);
            flash('error', 'Agrega al menos una línea con producto y cantidad.');
            redirect('/admin/cotizaciones/crear');
        }

        $totals = $this->totals($items, $header['tax_rate']);
        $send = (($_POST['action'] ?? 'guardar') === 'enviar');

        if ($send && ($header['customer_email'] === '' || !filter_var($header['customer_email'], FILTER_VALIDATE_EMAIL))) {
            remember_old($_POST);
            flash('error', 'Para enviar la cotización necesitas un correo de cliente válido.');
            redirect('/admin/cotizaciones/crear');
        }

        $id = Quote::create([
            'quote_number' => Quote::nextNumber(),
            'customer_rut' => $header['customer_rut'] !== '' ? $header['customer_rut'] : null,
            'customer_company' => $header['customer_company'],
            'customer_address' => $header['customer_address'] !== '' ? $header['customer_address'] : null,
            'customer_email' => $header['customer_email'] !== '' ? $header['customer_email'] : null,
            'customer_phone' => $header['customer_phone'] !== '' ? $header['customer_phone'] : null,
            'contact_person' => $header['contact_person'] !== '' ? $header['contact_person'] : null,
            'subtotal' => $totals['subtotal'],
            'tax_rate' => $header['tax_rate'],
            'tax' => $totals['tax'],
            'total' => $totals['total'],
            'notes' => $header['notes'] !== '' ? $header['notes'] : null,
            'status' => $send ? 'enviada' : 'borrador',
        ]);

        $this->saveItems($id, $items);

        if ($send) {
            $this->sendEmail($id);
            flash('success', 'Cotización enviada por correo.');
        } else {
            flash('success', 'Cotización guardada como borrador.');
        }
        redirect('/admin/cotizaciones/' . $id);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $quote = Quote::withItems($id);
        if (!$quote) {
            flash('error', 'Cotización no encontrada.');
            redirect('/admin/cotizaciones');
        }
        $this->view('admin/quotes/form', [
            'pageTitle' => 'Editar cotización ' . $quote['quote_number'],
            'quote' => $quote,
            'products' => Product::forSelect(),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/cotizaciones');
        }

        $header = $this->headerFromRequest();
        if ($header['customer_company'] === '') {
            remember_old($_POST);
            flash('error', 'El nombre de la empresa es obligatorio.');
            redirect('/admin/cotizaciones/editar/' . $id);
        }

        $items = $this->itemsFromRequest();
        if (!$items) {
            remember_old($_POST);
            flash('error', 'Agrega al menos una línea con producto y cantidad.');
            redirect('/admin/cotizaciones/editar/' . $id);
        }

        $totals = $this->totals($items, $header['tax_rate']);
        $send = (($_POST['action'] ?? 'guardar') === 'enviar');

        if ($send && ($header['customer_email'] === '' || !filter_var($header['customer_email'], FILTER_VALIDATE_EMAIL))) {
            remember_old($_POST);
            flash('error', 'Para enviar la cotización necesitas un correo de cliente válido.');
            redirect('/admin/cotizaciones/editar/' . $id);
        }

        Quote::update($id, [
            'customer_rut' => $header['customer_rut'] !== '' ? $header['customer_rut'] : null,
            'customer_company' => $header['customer_company'],
            'customer_address' => $header['customer_address'] !== '' ? $header['customer_address'] : null,
            'customer_email' => $header['customer_email'] !== '' ? $header['customer_email'] : null,
            'customer_phone' => $header['customer_phone'] !== '' ? $header['customer_phone'] : null,
            'contact_person' => $header['contact_person'] !== '' ? $header['contact_person'] : null,
            'subtotal' => $totals['subtotal'],
            'tax_rate' => $header['tax_rate'],
            'tax' => $totals['tax'],
            'total' => $totals['total'],
            'notes' => $header['notes'] !== '' ? $header['notes'] : null,
        ]);

        QuoteItem::deleteForQuote($id);
        $this->saveItems($id, $items);

        if ($send) {
            Quote::update($id, ['status' => 'enviada']);
            $this->sendEmail($id);
            flash('success', 'Cotización actualizada y enviada por correo.');
        } else {
            flash('success', 'Cotización actualizada.');
        }
        redirect('/admin/cotizaciones/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireLogin();
        $quote = Quote::withItems($id);
        if (!$quote) {
            flash('error', 'Cotización no encontrada.');
            redirect('/admin/cotizaciones');
        }
        $this->view('admin/quotes/show', [
            'pageTitle' => 'Cotización ' . $quote['quote_number'],
            'quote' => $quote,
            'statuses' => Quote::STATUSES,
        ], 'admin');
    }

    public function updateStatus(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/cotizaciones');
        }
        $status = trim($_POST['status'] ?? '');
        if (in_array($status, Quote::STATUSES, true)) {
            Quote::update($id, ['status' => $status]);
            flash('success', 'Estado actualizado.');
        }
        redirect('/admin/cotizaciones/' . $id);
    }

    public function send(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/cotizaciones');
        }
        $quote = Quote::withItems($id);
        if (!$quote) {
            flash('error', 'Cotización no encontrada.');
            redirect('/admin/cotizaciones');
        }
        if (empty($quote['customer_email'])) {
            flash('error', 'Esta cotización no tiene correo de cliente. Edítala y agrega uno.');
            redirect('/admin/cotizaciones/' . $id);
        }
        $this->sendEmail($id);
        Quote::update($id, ['status' => 'enviada']);
        flash('success', 'Cotización enviada por correo.');
        redirect('/admin/cotizaciones/' . $id);
    }

    public function pdf(int $id): void
    {
        Auth::requireLogin();
        $quote = Quote::withItems($id);
        if (!$quote) {
            flash('error', 'Cotización no encontrada.');
            redirect('/admin/cotizaciones');
        }
        $pdf = QuotePdf::render($quote);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $quote['quote_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Sesión inválida.');
            redirect('/admin/cotizaciones');
        }
        Quote::delete($id);
        flash('success', 'Cotización eliminada.');
        redirect('/admin/cotizaciones');
    }

    // --- Helpers ---

    private function headerFromRequest(): array
    {
        return [
            'customer_rut' => trim($_POST['customer_rut'] ?? ''),
            'customer_company' => trim($_POST['customer_company'] ?? ''),
            'customer_address' => trim($_POST['customer_address'] ?? ''),
            'customer_email' => trim($_POST['customer_email'] ?? ''),
            'customer_phone' => trim($_POST['customer_phone'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'tax_rate' => $this->taxRateFromRequest(),
        ];
    }

    private function taxRateFromRequest(): float
    {
        $rate = (float) ($_POST['tax_rate'] ?? 19);
        if ($rate < 0 || $rate > 100) {
            $rate = 19;
        }
        return $rate;
    }

    private function itemsFromRequest(): array
    {
        $productIds = $_POST['items']['product_id'] ?? [];
        $quantities = $_POST['items']['quantity'] ?? [];
        $prices = $_POST['items']['unit_price'] ?? [];
        $notes = $_POST['items']['notes'] ?? [];

        $items = [];
        $count = count($productIds);
        for ($i = 0; $i < $count; $i++) {
            $productId = (int) ($productIds[$i] ?? 0);
            $quantity = (int) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            if ($productId <= 0 || $quantity <= 0 || $price < 0) {
                continue;
            }
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }
            $items[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit_price' => $price,
                'quantity' => $quantity,
                'line_total' => round($price * $quantity, 2),
                'notes' => trim((string) ($notes[$i] ?? '')),
            ];
        }
        return $items;
    }

    private function totals(array $items, float $taxRate): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['line_total'];
        }
        $subtotal = round($subtotal, 2);
        $tax = round($subtotal * $taxRate / 100, 2);
        $total = round($subtotal + $tax, 2);
        return ['subtotal' => $subtotal, 'tax' => $tax, 'total' => $total];
    }

    private function saveItems(int $quoteId, array $items): void
    {
        foreach ($items as $item) {
            QuoteItem::create([
                'quote_id' => $quoteId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
                'notes' => $item['notes'] !== '' ? $item['notes'] : null,
            ]);
        }
    }

    private function sendEmail(int $id): void
    {
        $quote = Quote::withItems($id);
        if (!$quote || empty($quote['customer_email'])) {
            return;
        }
        $to = (string) $quote['customer_email'];
        $subject = 'Cotización ' . $quote['quote_number'] . ' — ' . $quote['customer_company'];
        $body = $this->emailBody($quote);

        try {
            $pdf = QuotePdf::render($quote);
            $this->mailWithAttachment($to, $subject, $body, $quote['quote_number'] . '.pdf', $pdf);
        } catch (\Throwable $e) {
            send_mail($to, $subject, $body);
        }
    }

    private function emailBody(array $quote): string
    {
        $lines = [];
        $lines[] = 'Estimado/a ' . ($quote['contact_person'] ?: 'equipo') . ',';
        $lines[] = '';
        $lines[] = 'Adjuntamos la cotización ' . $quote['quote_number'] . ' de ' . $quote['customer_company'] . '.';
        $lines[] = '';
        $lines[] = 'Detalle:';
        foreach ($quote['items'] as $it) {
            $lines[] = ' - ' . $it['quantity'] . ' x ' . $it['product_name'] . ' = ' . money($it['line_total']);
        }
        $lines[] = '';
        $lines[] = 'Subtotal (neto): ' . money($quote['subtotal']);
        $lines[] = 'IVA (' . format_tax_rate($quote['tax_rate']) . '%): ' . money($quote['tax']);
        $lines[] = 'Total (bruto): ' . money($quote['total']);
        if (!empty($quote['notes'])) {
            $lines[] = '';
            $lines[] = 'Indicaciones:';
            $lines[] = $quote['notes'];
        }
        $lines[] = '';
        $lines[] = 'Quedamos atentos a tus comentarios.';
        $lines[] = config('app_name', 'KAMAQ');
        return implode("\n", $lines);
    }

    private function mailWithAttachment(string $to, string $subject, string $body, string $filename, string $pdf): void
    {
        $from = (string) config('contact_email', 'contacto@kamaq.cl');
        $boundary = '=_kamaq_' . bin2hex(random_bytes(12));
        $content = base64_encode($pdf);

        $message = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $body . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: application/pdf; name=\"{$filename}\"\r\n"
            . "Content-Disposition: attachment; filename=\"{$filename}\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split($content, 76, "\r\n")
            . "--{$boundary}--";

        $headers = "From: {$from}\r\n"
            . "Reply-To: {$from}\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
        @mail($to, $subject, $message, $headers);
    }
}
