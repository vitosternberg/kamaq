<?php

namespace App\Models;

/**
 * Cálculo de envío según domicilio (RM vs fuera) y subtotal.
 * Los montos se configuran en /admin/envios (tabla settings).
 */
class Shipping
{
    public static function settings(): array
    {
        return [
            'rm_price' => (float) Setting::get('shipping_rm_price', '3990'),
            'free_threshold' => (float) Setting::get('shipping_free_threshold', '15000'),
            'express_price' => (float) Setting::get('shipping_express_price', '4990'),
            'outside_price' => (float) Setting::get('shipping_outside_price', '6990'),
        ];
    }

    /**
     * Opciones de envío disponibles para un cliente.
     */
    public static function options(bool $isRm, float $subtotal): array
    {
        $s = self::settings();

        if ($isRm) {
            $standard = $subtotal >= $s['free_threshold'] ? 0.0 : $s['rm_price'];
            return [
                [
                    'key' => 'standard',
                    'name' => $standard > 0 ? 'Envío estándar (RM)' : 'Envío estándar (RM) — Gratis',
                    'price' => $standard,
                ],
                [
                    'key' => 'express',
                    'name' => 'Express (RM)',
                    'price' => $s['express_price'],
                ],
            ];
        }

        return [
            [
                'key' => 'outside',
                'name' => 'Envío fuera de la RM',
                'price' => $s['outside_price'],
            ],
        ];
    }

    public static function priceFor(string $key, bool $isRm, float $subtotal): float
    {
        foreach (self::options($isRm, $subtotal) as $option) {
            if ($option['key'] === $key) {
                return $option['price'];
            }
        }
        $all = self::options($isRm, $subtotal);
        return $all ? $all[0]['price'] : 0.0;
    }
}
