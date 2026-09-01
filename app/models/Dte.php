<?php

namespace App\Models;

use App\Core\LibreDte;
use App\Core\Model;

// Emisión de boleta (39) / factura (33) electrónica vía LibreDTE, ambiente certificación.
// Siempre NO BLOQUEANTE: emitForOrder() nunca lanza excepción hacia arriba.
class Dte extends Model
{
    protected static string $table = 'dtes';

    /**
     * Orquestador de emisión. Registra el estado en la tabla dtes y NO lanza
     * excepciones: todo error queda como estado 'error' con glosa.
     */
    public static function emitForOrder(int $orderId): void
    {
        $rowId = null;
        $tipo = null;
        try {
            $order = Order::withItems($orderId);
            if (!$order) {
                return;
            }
            $tipo = (($order['doc_type'] ?? 'boleta') === 'factura') ? 33 : 39;

            // Idempotencia: ya emitido → no hacer nada. Estados no emitidos → reintento.
            $existing = self::findForOrder($orderId, $tipo);
            if ($existing !== null) {
                if (($existing['estado'] ?? '') === 'emitido') {
                    return;
                }
                $rowId = (int) $existing['id'];
                static::update($rowId, ['estado' => 'pendiente', 'glosa' => null]);
            } else {
                try {
                    $rowId = static::create([
                        'order_id' => $orderId,
                        'tipo' => $tipo,
                        'estado' => 'pendiente',
                        'certificacion' => config('libredte_certificacion', true) ? 1 : 0,
                    ]);
                } catch (\Throwable $e) {
                    // La UNIQUE (order_id, tipo) es la barrera ante emisiones concurrentes:
                    // si otro proceso ya creó la fila, no emitir de nuevo.
                    error_log('Dte: emisión ya en curso para pedido ' . $orderId . ' (tipo ' . $tipo . ').');
                    return;
                }
            }

            $hash = (string) config('libredte_hash', '');
            if ($hash === '') {
                static::update($rowId, [
                    'estado' => 'config_pendiente',
                    'glosa' => 'Falta configurar libredte_hash en config.local.php.',
                ]);
                return;
            }

            // Si ya existe un código temporal guardado (emitir ya se ejecutó antes),
            // NO re-llamar "emitir": continuar directo a "generar".
            $codigo = $existing !== null ? (string) ($existing['codigo'] ?? '') : '';
            $folioTemporal = 0;

            if ($codigo === '') {
                $temporal = LibreDte::emitir(self::buildPayload($order, $tipo));
                if (!is_array($temporal) || empty($temporal['codigo'])) {
                    throw new \RuntimeException('LibreDTE: emitir no devolvió un código temporal.');
                }
                $codigo = (string) $temporal['codigo'];
                $folioTemporal = (int) ($temporal['folio'] ?? 0);
                static::update($rowId, ['codigo' => $codigo]);
            }

            // Paso 2: timbrar, firmar y enviar al SII.
            $dte = self::buildPayload($order, $tipo);
            $receptorRut = (string) ($dte['Encabezado']['Receptor']['RUTRecep'] ?? '66666666-6');
            $real = LibreDte::generar((string) config('emisor_rut', ''), $receptorRut, $dte, $codigo);
            if (!is_array($real)) {
                throw new \RuntimeException('LibreDTE: generar no respondió (revisa error_log).');
            }

            $folio = (int) ($real['folio'] ?? $folioTemporal);
            $trackId = (string) ($real['track_id'] ?? '');
            $emisorRut = (string) config('emisor_rut', '');
            $pdfUrl = LibreDte::pdfUrl($tipo, $folio, $emisorRut);

            static::update($rowId, [
                'folio' => $folio > 0 ? $folio : null,
                'track_id' => $trackId !== '' ? $trackId : null,
                'codigo' => $codigo,
                'estado' => 'emitido',
                'pdf_url' => $pdfUrl,
                'glosa' => null,
            ]);

            // Paso 3 (opcional): adjuntar el PDF por correo. Un fallo aquí NO revierte la emisión.
            if (config('dte_email_pdf', true) && !empty($order['customer_email'])) {
                try {
                    $pdf = LibreDte::pdfBinary($tipo, $folio, $emisorRut);
                    if (is_string($pdf) && $pdf !== '') {
                        $filename = 'DTE_' . $tipo . '_' . $folio . '.pdf';
                        $subject = 'Tu documento tributario (DTE) — ' . (string) config('app_name', 'KAMAQ');
                        $body = "Hola " . (string) ($order['customer_name'] ?? '')
                            . ",\n\nAdjuntamos tu documento tributario electrónico (folio " . $folio . ").\n\n"
                            . "Gracias por comprar en " . (string) config('app_name', 'KAMAQ') . ".";
                        send_mail_attachment((string) $order['customer_email'], $subject, $body, $filename, $pdf);
                    }
                } catch (\Throwable $e) {
                    error_log('Dte: no se pudo enviar el PDF por correo: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            self::recordError($orderId, $tipo, $rowId, $e);
        }
    }

    /**
     * Fila DTE de un pedido (para la vista admin), o null si no existe.
     */
    public static function forOrder(int $orderId): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM dtes WHERE order_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findForOrder(int $orderId, int $tipo): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM dtes WHERE order_id = ? AND tipo = ? LIMIT 1');
        $stmt->execute([$orderId, $tipo]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    private static function recordError(int $orderId, ?int $tipo, ?int $rowId, \Throwable $e): void
    {
        try {
            $message = mb_substr($e->getMessage(), 0, 255);
            error_log('Dte emitForOrder(' . $orderId . '): ' . $message);

            if ($rowId !== null) {
                static::update($rowId, ['estado' => 'error', 'glosa' => $message]);
                return;
            }
            if ($tipo === null) {
                return; // no se pudo leer el pedido; no hay fila que marcar.
            }
            $existing = self::findForOrder($orderId, $tipo);
            if ($existing !== null) {
                static::update((int) $existing['id'], ['estado' => 'error', 'glosa' => $message]);
                return;
            }
            static::create([
                'order_id' => $orderId,
                'tipo' => $tipo,
                'estado' => 'error',
                'glosa' => $message,
                'certificacion' => config('libredte_certificacion', true) ? 1 : 0,
            ]);
        } catch (\Throwable $inner) {
            error_log('Dte: no se pudo registrar el error en BD: ' . $inner->getMessage());
        }
    }

    /**
     * Construye el payload LibreDTE. Precios del carrito son NETOS
     * (subtotal = neto; IVA se suma aparte; envío va como exento).
     */
    private static function buildPayload(array $order, int $tipo): array
    {
        $receptor = [];
        if ($tipo === 33) {
            // Factura: receptor obligatorio con datos de la empresa.
            $receptor = [
                'RUTRecep' => (string) ($order['doc_rut'] ?? ''),
                'RznSocRecep' => (string) ($order['doc_company'] ?? ''),
                'GiroRecep' => (string) ($order['doc_giro'] ?? ''),
                'DirRecep' => (string) ($order['address'] ?? ''),
                'CmnaRecep' => (string) ($order['city'] ?? ''),
            ];
        } else {
            // Boleta: receptor puede omitirse; usar RUT genérico si no hay datos.
            $rut = (string) ($order['doc_rut'] ?? '');
            $receptor = $rut !== ''
                ? ['RUTRecep' => $rut, 'RznSocRecep' => (string) ($order['customer_name'] ?? '')]
                : ['RUTRecep' => '66666666-6', 'RznSocRecep' => 'Consumidor final'];
        }

        $detalle = [];
        foreach ($order['items'] as $item) {
            $detalle[] = [
                'NmbItem' => (string) ($item['product_name'] ?? ''),
                'QtyItem' => (int) ($item['quantity'] ?? 0),
                'PrcItem' => (int) round((float) ($item['price'] ?? 0)),
                'MontoItem' => (int) round((float) ($item['subtotal'] ?? 0)),
            ];
        }
        $shipping = (float) ($order['shipping'] ?? 0);
        if ($shipping > 0) {
            $detalle[] = [
                'NmbItem' => 'Envío',
                'IndExe' => 1,
                'QtyItem' => 1,
                'PrcItem' => (int) round($shipping),
                'MontoItem' => (int) round($shipping),
            ];
        }

        return [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $tipo,
                    'Folio' => 0,
                    'FchEmis' => date('Y-m-d'),
                ],
                'Emisor' => [
                    'RUTEmisor' => (string) config('emisor_rut', ''),
                    'RznSoc' => (string) config('emisor_razon_social', ''),
                    'GiroEmis' => (string) config('emisor_giro', ''),
                    'Acteco' => (string) config('emisor_acteco', ''),
                    'DirOrigen' => (string) config('emisor_direccion', ''),
                    'CmnaOrigen' => (string) config('emisor_comuna', ''),
                    'CiudadOrigen' => (string) config('emisor_ciudad', ''),
                    'Telefono' => (string) config('emisor_telefono', ''),
                    'CorreoEmisor' => (string) config('emisor_email', ''),
                ],
                'Receptor' => $receptor,
                'Totales' => [
                    'MntNeto' => (int) round((float) ($order['subtotal'] ?? 0)),
                    'TasaIVA' => 19,
                    'IVA' => (int) round((float) ($order['tax'] ?? 0)),
                    'MntExe' => (int) round($shipping),
                    'MntTotal' => (int) round((float) ($order['total'] ?? 0)),
                ],
            ],
            'Detalle' => $detalle,
        ];
    }
}
