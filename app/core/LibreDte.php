<?php

declare(strict_types=1);

namespace App\Core;

// Integración REST directa (cURL) con LibreDTE (boleta/factura electrónica), sin Composer.
// Ambiente de CERTIFICACIÓN por defecto (config libredte_certificacion = true).
class LibreDte
{
    private const ENDPOINT_EMITIR = '/dte/documentos/emitir';
    private const ENDPOINT_GENERAR = '/dte/documentos/generar';
    private const ENDPOINT_PDF = '/dte/dte_emitidos/pdf';
    private const ENDPOINT_ESTADO = '/dte/dte_emitidos/actualizar_estado';

    /**
     * Paso 1: crea el DTE temporal (no se envía al SII). Devuelve folio + "codigo".
     *
     * @return array<string, mixed>|null
     */
    public static function emitir(array $dte): ?array
    {
        return self::request('POST', self::ENDPOINT_EMITIR . '?normalizar=0&formato=json&links=0&email=0', $dte);
    }

    /**
     * Paso 2: timbra, firma y envía al SII el DTE temporal.
     *
     * @return array<string, mixed>|null
     */
    public static function generar(string $emisorRut, string $receptorRut, array $dte, string $codigo): ?array
    {
        $body = [
            'emisor' => ['RUTEmisor' => $emisorRut],
            'receptor' => ['RUTRecep' => $receptorRut],
            'dte' => $dte,
            'codigo' => $codigo,
            'certificacion' => (int) (config('libredte_certificacion', true) ? 1 : 0),
        ];
        return self::request('POST', self::ENDPOINT_GENERAR . '?getXML=0&links=0&email=0&retry=10&gzip=0', $body);
    }

    /**
     * URL del PDF del DTE emitido (requiere auth Basic de LibreDTE).
     */
    public static function pdfUrl(int $tipo, int $folio, string $emisorRut): string
    {
        $base = rtrim((string) config('libredte_api_base', ''), '/');
        return $base . self::ENDPOINT_PDF . '/' . (int) $tipo . '/' . (int) $folio . '/'
            . rawurlencode($emisorRut) . '?formato=general&papelContinuo=0';
    }

    /**
     * Consulta el estado del envío al SII (metodo=1: webservice SII).
     *
     * @return array<string, mixed>|null
     */
    public static function estado(int $tipo, int $folio, string $emisorRut): ?array
    {
        return self::request('GET', self::ENDPOINT_ESTADO . '/' . (int) $tipo . '/' . (int) $folio . '/'
            . rawurlencode($emisorRut) . '/1');
    }

    /**
     * Descarga el PDF del DTE emitido (binario) para adjuntarlo por correo.
     */
    public static function pdfBinary(int $tipo, int $folio, string $emisorRut): ?string
    {
        $base = rtrim((string) config('libredte_api_base', ''), '/');
        if ($base === '') {
            throw new \RuntimeException('LibreDTE: libredte_api_base no configurado.');
        }

        $ch = curl_init(self::pdfUrl($tipo, $folio, $emisorRut));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode('X:' . self::hash()),
                'Accept: application/pdf',
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '' || $httpCode !== 200) {
            error_log('LibreDTE: error al descargar PDF (HTTP ' . $httpCode . '). ' . $error);
            return null;
        }

        return (string) $response;
    }

    /**
     * Hash de la cuenta LibreDTE (config.libredte_hash). Vacío = cuenta sin configurar.
     */
    private static function hash(): string
    {
        $hash = (string) config('libredte_hash', '');
        if ($hash === '') {
            throw new \RuntimeException('LibreDTE: libredte_hash no configurado (completar en config.local.php).');
        }
        return $hash;
    }

    /**
     * Petición JSON con autenticación Basic (usuario = hash, contraseña = 'X').
     *
     * @return array<string, mixed>|null
     */
    private static function request(string $method, string $path, ?array $body = null): ?array
    {
        $base = rtrim((string) config('libredte_api_base', ''), '/');
        if ($base === '') {
            throw new \RuntimeException('LibreDTE: libredte_api_base no configurado.');
        }

        $url = $base . '/' . ltrim($path, '/');

        $headers = [
            'Authorization: Basic ' . base64_encode('X:' . self::hash()),
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $jsonBody = null;
        if ($body !== null) {
            $jsonBody = json_encode($body);
            if ($jsonBody === false) {
                throw new \RuntimeException('LibreDTE: no se pudo serializar el cuerpo de la petición.');
            }
            $headers[] = 'Content-Length: ' . strlen($jsonBody);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ]);

        if ($jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '') {
            // No exponer credenciales; solo el motivo del error de red/HTTP.
            error_log('LibreDTE: error en la petición HTTP (HTTP ' . $httpCode . '). ' . $error);
            return null;
        }

        if ($httpCode !== 200) {
            $message = 'LibreDTE: respuesta HTTP ' . $httpCode;
            $decoded = json_decode((string) $response, true);
            if (is_array($decoded)) {
                $server = $decoded['error'] ?? $decoded['message'] ?? null;
                if (is_string($server) && $server !== '') {
                    $message .= ': ' . $server;
                }
            }
            error_log($message);
            throw new \RuntimeException($message);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            error_log('LibreDTE: respuesta no decodificable como JSON.');
            return null;
        }

        return $decoded;
    }
}
