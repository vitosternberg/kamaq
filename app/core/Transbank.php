<?php

declare(strict_types=1);

namespace App\Core;

// Integración REST directa (cURL) con Transbank Webpay Plus (sin Composer).
class Transbank
{
    private const BASE_PATH = '/rswebpaytransaction/api/webpay/v1.3/transactions';

    /**
     * Inicia una transacción Webpay Plus.
     *
     * @return array{token: string, url: string}|null
     */
    public static function create(string $buyOrder, string $sessionId, int $amount, string $returnUrl): ?array
    {
        $body = [
            'buy_order' => (string) $buyOrder,
            'session_id' => (string) $sessionId,
            'amount' => $amount,
            'return_url' => (string) $returnUrl,
        ];
        return self::request('POST', self::BASE_PATH, $body);
    }

    /**
     * Confirma una transacción ya iniciada con su token.
     *
     * @return array<string, mixed>|null
     */
    public static function commit(string $token): ?array
    {
        return self::request('PUT', self::BASE_PATH . '/' . rawurlencode($token));
    }

    /**
     * Consulta el estado de una transacción por su token.
     *
     * @return array<string, mixed>|null
     */
    public static function status(string $token): ?array
    {
        return self::request('GET', self::BASE_PATH . '/' . rawurlencode($token));
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function request(string $method, string $path, ?array $body = null): ?array
    {
        $base = rtrim((string) config('tbk_api_base', ''), '/');
        if ($base === '') {
            error_log('Transbank: tbk_api_base no configurado.');
            return null;
        }

        $url = $base . '/' . ltrim($path, '/');

        $headers = [
            'Tbk-Api-Key-Id: ' . (string) config('tbk_api_key_id', ''),
            'Tbk-Api-Key-Secret: ' . (string) config('tbk_api_key_secret', ''),
            'Content-Type: application/json',
        ];

        $jsonBody = null;
        if ($body !== null) {
            $jsonBody = json_encode($body);
            if ($jsonBody === false) {
                error_log('Transbank: no se pudo serializar el cuerpo de la petición.');
                return null;
            }
            $headers[] = 'Content-Length: ' . strlen($jsonBody);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '') {
            // No exponer el secret; solo el motivo del error de red/HHTP.
            error_log('Transbank: error en la petición HTTP (HTTP ' . $httpCode . '). ' . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('Transbank: respuesta HTTP ' . $httpCode . ' desde la API.');
            return null;
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            error_log('Transbank: respuesta no decodificable como JSON.');
            return null;
        }

        return $decoded;
    }
}
